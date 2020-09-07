<?php

declare(strict_types=1);

namespace Rector\Naming\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\BooleanType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Naming\Naming\MethodNameResolver;
use Rector\NodeCollector\NodeFinder\MethodCallParsedNodesFinder;

/**
 * @see \Rector\Naming\Tests\Rector\ClassMethod\MakeIsserClassMethodNameStartWithIsRector\MakeIsserClassMethodNameStartWithIsRectorTest
 */
final class MakeIsserClassMethodNameStartWithIsRector extends AbstractRector
{
    /**
     * @var string
     */
    private const ISSER_NAME_PATTERN = '#^(is|has|was|must|should|__)#';

    /**
     * @var MethodNameResolver
     */
    private $methodNameResolver;

    /**
     * @var MethodCallParsedNodesFinder
     */
    private $methodCallParsedNodesFinder;

    public function __construct(
        MethodNameResolver $methodNameResolver,
        MethodCallParsedNodesFinder $methodCallParsedNodesFinder
    ) {
        $this->methodNameResolver = $methodNameResolver;
        $this->methodCallParsedNodesFinder = $methodCallParsedNodesFinder;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change is method names to start with is/has/was', [
            new CodeSample(
                <<<'PHP'
declare(strict_types=1);

class SomeClass
{
    /**
     * @var bool
     */
    private $isActive = false;

    public function getIsActive()
    {
        return $this->isActive;
    }
}
PHP

                ,
                <<<'PHP'
declare(strict_types=1);

class SomeClass
{
    /**
     * @var bool
     */
    private $isActive = false;

    public function isActive()
    {
        return $this->isActive;
    }
}
PHP

            ),
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
        if ($this->isAlreadyIsserNamedClassMethod($node)) {
            return null;
        }

        $getterClassMethodReturnedExpr = $this->matchIsserClassMethodReturnedExpr($node);
        if ($getterClassMethodReturnedExpr === null) {
            return null;
        }

        $isserMethodName = $this->methodNameResolver->resolveIsserFromReturnedExpr($getterClassMethodReturnedExpr);
        if ($isserMethodName === null) {
            return null;
        }

        if ($this->isName($node->name, $isserMethodName)) {
            return null;
        }

        $node->name = new Identifier($isserMethodName);

        $this->updateClassMethodCalls($node, $isserMethodName);

        return $node;
    }

    private function isAlreadyIsserNamedClassMethod(ClassMethod $classMethod): bool
    {
        return $this->isName($classMethod, self::ISSER_NAME_PATTERN);
    }

    private function matchIsserClassMethodReturnedExpr(ClassMethod $classMethod): ?Expr
    {
        if (count((array) $classMethod->stmts) !== 1) {
            return null;
        }

        $onlyStmt = $classMethod->stmts[0];
        if (! $onlyStmt instanceof Return_) {
            return null;
        }

        if (! $onlyStmt->expr instanceof PropertyFetch) {
            return null;
        }

        $propertyStaticType = $this->getStaticType($onlyStmt->expr);
        if (! $propertyStaticType instanceof BooleanType) {
            return null;
        }

        return $onlyStmt->expr;
    }

    private function updateClassMethodCalls(ClassMethod $classMethod, string $newClassMethodName): void
    {
        /** @var MethodCall[] $methodCalls */
        $methodCalls = $this->methodCallParsedNodesFinder->findByClassMethod($classMethod);
        foreach ($methodCalls as $methodCall) {
            $methodCall->name = new Identifier($newClassMethodName);
        }
    }
}
