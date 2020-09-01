<?php

declare(strict_types=1);

namespace Rector\Renaming\Rector\ClassConstFetch;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Renaming\ValueObject\ClassConstantRename;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Renaming\Tests\Rector\ClassConstFetch\RenameClassConstantRector\RenameClassConstantRectorTest
 */
final class RenameClassConstantRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const CLASS_CONSTANT_RENAME = 'constant_rename';

    /**
     * @var ClassConstantRename[]
     */
    private $classConstantRenames = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces defined class constants in their calls.', [
            new ConfiguredCodeSample(
                <<<'PHP'
$value = SomeClass::OLD_CONSTANT;
$value = SomeClass::OTHER_OLD_CONSTANT;
PHP
                ,
                <<<'PHP'
$value = SomeClass::NEW_CONSTANT;
$value = DifferentClass::NEW_CONSTANT;
PHP
                ,
                [
                    self::CLASS_CONSTANT_RENAME => [
                        new ClassConstantRename('SomeClass', 'OLD_CONSTANT', 'NEW_CONSTANT'),
                        new ClassConstantRename('SomeClass', 'OTHER_OLD_CONSTANT', 'DifferentClass::NEW_CONSTANT'),
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassConstFetch::class];
    }

    /**
     * @param ClassConstFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->classConstantRenames as $classConstantRename) {
            if (! $this->isObjectType($node, $classConstantRename->getOldClass())) {
                continue;
            }

            if (! $this->isName($node->name, $classConstantRename->getOldConstant())) {
                continue;
            }

            if (Strings::contains($classConstantRename->getNewConstant(), '::')) {
                return $this->createClassConstantFetchNodeFromDoubleColonFormat($classConstantRename->getNewConstant());
            }

            $node->name = new Identifier($classConstantRename->getNewConstant());

            return $node;
        }

        return $node;
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        $classConstantRenames = $configuration[self::CLASS_CONSTANT_RENAME] ?? [];
        Assert::allIsInstanceOf($classConstantRenames, ClassConstantRename::class);
        $this->classConstantRenames = $classConstantRenames;
    }

    private function createClassConstantFetchNodeFromDoubleColonFormat(string $constant): ClassConstFetch
    {
        [$constantClass, $constantName] = explode('::', $constant);

        return new ClassConstFetch(new FullyQualified($constantClass), new Identifier($constantName));
    }
}
