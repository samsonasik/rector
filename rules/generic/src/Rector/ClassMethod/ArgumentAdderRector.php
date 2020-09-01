<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\ClassMethod;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Generic\ValueObject\AddedArgument;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Generic\Tests\Rector\ClassMethod\ArgumentAdderRector\ArgumentAdderRectorTest
 */
final class ArgumentAdderRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const ADDED_ARGUMENTS = 'added_arguments';

    /**
     * @var string
     */
    public const SCOPE_PARENT_CALL = 'parent_call';

    /**
     * @var string
     */
    public const SCOPE_METHOD_CALL = 'method_call';

    /**
     * @var string
     */
    public const SCOPE_CLASS_METHOD = 'class_method';

    /**
     * @var AddedArgument[]
     */
    private $addedArguments = [];

    public function getDefinition(): RectorDefinition
    {
        $exampleConfiguration = [
            self::ADDED_ARGUMENTS => [
                new AddedArgument('SomeExampleClass', 'someMethod', 0, 'someArgument', 'true', 'SomeType'),
            ],
        ];

        return new RectorDefinition(
            'This Rector adds new default arguments in calls of defined methods and class types.',
            [
                new ConfiguredCodeSample(
                    <<<'PHP'
$someObject = new SomeExampleClass;
$someObject->someMethod();
PHP
                    ,
                    <<<'PHP'
$someObject = new SomeExampleClass;
$someObject->someMethod(true);
PHP
                    ,
                    $exampleConfiguration
                ),
                new ConfiguredCodeSample(
                    <<<'PHP'
class MyCustomClass extends SomeExampleClass
{
    public function someMethod()
    {
    }
}
PHP
                    ,
                    <<<'PHP'
class MyCustomClass extends SomeExampleClass
{
    public function someMethod($value = true)
    {
    }
}
PHP
                    ,
                    $exampleConfiguration
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class, ClassMethod::class];
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->addedArguments as $addedArgument) {
            if (! $this->isObjectTypeMatch($node, $addedArgument->getClass())) {
                continue;
            }

            if (! $this->isName($node->name, $addedArgument->getMethod())) {
                continue;
            }

            $this->processPositionWithDefaultValues($node, $addedArgument);
        }

        return $node;
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        $addedArguments = $configuration[self::ADDED_ARGUMENTS] ?? [];
        Assert::allIsInstanceOf($addedArguments, AddedArgument::class);
        $this->addedArguments = $addedArguments;
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     */
    private function isObjectTypeMatch(Node $node, string $type): bool
    {
        if ($node instanceof MethodCall) {
            return $this->isObjectType($node->var, $type);
        }

        if ($node instanceof StaticCall) {
            return $this->isObjectType($node->class, $type);
        }

        // ClassMethod
        /** @var Class_|null $classLike */
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);

        // anonymous class
        if ($classLike === null) {
            return false;
        }

        return $this->isObjectType($classLike, $type);
    }

    /**
     * @param ClassMethod|MethodCall|StaticCall $node
     */
    private function processPositionWithDefaultValues(Node $node, AddedArgument $addedArgument): void
    {
        if ($this->shouldSkipParameter($node, $addedArgument)) {
            return;
        }

        $defaultValue = $addedArgument->getArgumentDefaultValue();
        $argumentType = $addedArgument->getArgumentType();

        $position = $addedArgument->getPosition();

        if ($node instanceof ClassMethod) {
            $argumentName = $addedArgument->getArgumentName();
            if ($argumentName === null) {
                throw new ShouldNotHappenException();
            }
            $this->addClassMethodParam($node, $argumentName, $defaultValue, $argumentType, $position);
        } elseif ($node instanceof StaticCall) {
            $argumentName = $addedArgument->getArgumentName();
            if ($argumentName === null) {
                throw new ShouldNotHappenException();
            }
            $this->processStaticCall($node, $position, $argumentName);
        } else {
            $arg = new Arg(BuilderHelpers::normalizeValue($defaultValue));
            $node->args[$position] = $arg;
        }
    }

    /**
     * @param ClassMethod|MethodCall|StaticCall $node
     */
    private function shouldSkipParameter(Node $node, AddedArgument $addedArgument): bool
    {
        $position = $addedArgument->getPosition();
        $argumentName = $addedArgument->getArgumentName();

        if ($node instanceof ClassMethod) {
            // already added?
            return isset($node->params[$position]) && $this->isName($node->params[$position], $argumentName);
        }

        // already added?
        if (isset($node->args[$position]) && $this->isName($node->args[$position], $argumentName)) {
            return true;
        }

        // is correct scope?
        return ! $this->isInCorrectScope($node, $addedArgument);
    }

    /**
     * @param mixed $defaultValue
     */
    private function addClassMethodParam(
        ClassMethod $classMethod,
        string $name,
        $defaultValue,
        ?string $type,
        int $position
    ): void {
        $param = new Param(new Variable($name), BuilderHelpers::normalizeValue($defaultValue));
        if ($type) {
            $param->type = ctype_upper($type[0]) ? new FullyQualified($type) : new Identifier($type);
        }

        $classMethod->params[$position] = $param;
    }

    private function processStaticCall(StaticCall $staticCall, int $position, string $name): void
    {
        if (! $staticCall->class instanceof Name) {
            return;
        }

        if (! $this->isName($staticCall->class, 'parent')) {
            return;
        }

        $staticCall->args[$position] = new Arg(new Variable($name));
    }

    /**
     * @param ClassMethod|MethodCall|StaticCall $node
     */
    private function isInCorrectScope(Node $node, AddedArgument $addedArgument): bool
    {
        if ($addedArgument->getScope() === null) {
            return true;
        }

        $scope = $addedArgument->getScope();

        if ($node instanceof ClassMethod) {
            return $scope === self::SCOPE_CLASS_METHOD;
        }

        if ($node instanceof StaticCall) {
            if (! $node->class instanceof Name) {
                return false;
            }

            if ($this->isName($node->class, 'parent')) {
                return $scope === self::SCOPE_PARENT_CALL;
            }

            return $scope === self::SCOPE_METHOD_CALL;
        }

        // MethodCall
        return $scope === self::SCOPE_METHOD_CALL;
    }
}
