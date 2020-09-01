<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\ValueObject\ParameterTypehint;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeDeclarationRector\AddParamTypeDeclarationRectorTest
 */
final class AddParamTypeDeclarationRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const PARAMETER_TYPEHINTS = 'parameter_typehintgs';

    /**
     * @var ParameterTypehint[]
     */
    private $parameterTypehints = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Add param types where needed', [
            new ConfiguredCodeSample(
                <<<'PHP'
class SomeClass
{
    public function process($name)
    {
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function process(string $name)
    {
    }
}
PHP
            , [
                self::PARAMETER_TYPEHINTS => [new ParameterTypehint('SomeClass', 'process', 0, 'string')],
            ]),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        /** @var ClassLike $classLike */
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);

        foreach ($this->parameterTypehints as $parameterTypehint) {
            if (! $this->isObjectType($classLike, $parameterTypehint->getClassName())) {
                continue;
            }

            if (! $this->isName($node, $parameterTypehint->getMethodName())) {
                continue;
            }

            $this->refactorClassMethodWithTypehintByParameterPosition($node, $parameterTypehint);
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $parameterTypehints = $configuration[self::PARAMETER_TYPEHINTS] ?? [];
        Assert::allIsInstanceOf($parameterTypehints, ParameterTypehint::class);
        $this->parameterTypehints = $parameterTypehints;
    }

    private function shouldSkip(ClassMethod $classMethod): bool
    {
        // skip class methods without args
        if (count((array) $classMethod->params) === 0) {
            return true;
        }

        /** @var ClassLike|null $classLike */
        $classLike = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if ($classLike === null) {
            return true;
        }

        // skip traits
        if ($classLike instanceof Trait_) {
            return true;
        }

        // skip class without parents/interfaces
        if ($classLike instanceof Class_) {
            if ($classLike->implements !== []) {
                return false;
            }

            if ($classLike->extends !== null) {
                return false;
            }

            return true;
        }

        // skip interface without parents
        /** @var Interface_ $classLike */
        return ! (bool) $classLike->extends;
    }

    private function refactorClassMethodWithTypehintByParameterPosition(
        ClassMethod $classMethod,
        ParameterTypehint $parameterTypehint
    ): void {
        $parameter = $classMethod->params[$parameterTypehint->getPosition()] ?? null;
        if ($parameter === null) {
            return;
        }

        $this->refactorParameter($parameter, $parameterTypehint);
    }

    private function refactorParameter(Param $param, ParameterTypehint $parameterTypehint): void
    {
        // already set → no change
        if ($param->type && $this->isName($param->type, $parameterTypehint->getTypehint())) {
            return;
        }

        // remove it
        if ($parameterTypehint->getTypehint() === '') {
            $param->type = null;
            return;
        }

        $returnTypeNode = $this->staticTypeMapper->mapStringToPhpParserNode($parameterTypehint->getTypehint());
        $param->type = $returnTypeNode;
    }
}
