<?php

declare(strict_types=1);

namespace Rector\Naming\Rector\PropertyProperty;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\Util\StaticRectorStrings;

/**
 * @see \Rector\Naming\Tests\Rector\PropertyProperty\RenamePropertyUnderscoreToCamelCaseRector\RenamePropertyUnderscoreToCamelCaseRectorTest
 */
final class RenamePropertyUnderscoreToCamelCaseRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Renames property with underscore to camel case', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    private $some_property;

    public function run(): void
    {
        $this->some_property;
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    private $someProperty;

    public function run(): void
    {
        $this->someProperty;
    }
}
CODE_SAMPLE

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [PropertyProperty::class, PropertyFetch::class];
    }

    /**
     * @param PropertyProperty|PropertyFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        $propertyName = $this->getName($node);
        if (strpos($propertyName, '_') === false) {
            return null;
        }

        $propertyName = StaticRectorStrings::underscoreToCamelCase($propertyName);
        $node->name = $propertyName;

        return $node;
    }
}