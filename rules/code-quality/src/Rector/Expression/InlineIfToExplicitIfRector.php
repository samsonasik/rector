<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\BooleanType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://3v4l.org/dmHCC
 *
 * @see \Rector\CodeQuality\Tests\Rector\Expression\InlineIfToExplicitIfRector\InlineIfToExplicitIfRectorTest
 */
final class InlineIfToExplicitIfRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change inline if to explicit if', [
            new CodeSample(
                <<<'PHP'
declare(strict_types=1);

class SomeClass
{
    public function run(): void
    {
        $userId = null;

        $userId === null && $userId = 5;
    }
}
PHP
,
                <<<'PHP'
declare(strict_types=1);

class SomeClass
{
    public function run(): void
    {
        $userId = null;

        if ($userId === null) {
            $userId = 5;
        }
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
        return [Expression::class];
    }

    /**
     * @param Expression $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->expr instanceof BooleanAnd) {
            return null;
        }

        $booleanAnd = $node->expr;

        $leftStaticType = $this->getStaticType($booleanAnd->left);
        if (! $leftStaticType instanceof BooleanType) {
            return null;
        }

        if (! $booleanAnd->right instanceof Assign) {
            return null;
        }

        $if = new If_($booleanAnd->left);
        $if->stmts[] = new Expression($booleanAnd->right);

        return $if;
    }
}
