<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\DoctrineCodeQuality\NodeAnalyzer\ColumnDatetimePropertyAnalyzer;
use Rector\DoctrineCodeQuality\NodeFactory\ValueAssignFactory;
use Rector\DoctrineCodeQuality\NodeManipulator\ColumnDatetimePropertyManipulator;
use Rector\DoctrineCodeQuality\NodeManipulator\ConstructorManipulator;
use Rector\NodeCollector\NodeFinder\ClassLikeParsedNodesFinder;

/**
 * @sponsor Thanks https://www.luzanky.cz/ for sponsoring this rule
 *
 * @see https://stackoverflow.com/a/7698687/1348344
 *
 * @see \Rector\DoctrineCodeQuality\Tests\Rector\Property\MoveCurrentDateTimeDefaultInEntityToConstructorRector\MoveCurrentDateTimeDefaultInEntityToConstructorRectorTest
 */
final class MoveCurrentDateTimeDefaultInEntityToConstructorRector extends AbstractRector
{
    /**
     * @var ColumnDatetimePropertyAnalyzer
     */
    private $columnDatetimePropertyAnalyzer;

    /**
     * @var ConstructorManipulator
     */
    private $constructorManipulator;

    /**
     * @var ValueAssignFactory
     */
    private $valueAssignFactory;

    /**
     * @var ColumnDatetimePropertyManipulator
     */
    private $columnDatetimePropertyManipulator;

    public function __construct(
        ClassLikeParsedNodesFinder $classLikeParsedNodesFinder,
        ColumnDatetimePropertyAnalyzer $columnDatetimePropertyAnalyzer,
        ConstructorManipulator $constructorManipulator,
        ValueAssignFactory $valueAssignFactory,
        ColumnDatetimePropertyManipulator $columnDatetimePropertyManipulator
    ) {
        $this->classLikeParsedNodesFinder = $classLikeParsedNodesFinder;
        $this->columnDatetimePropertyAnalyzer = $columnDatetimePropertyAnalyzer;
        $this->constructorManipulator = $constructorManipulator;
        $this->valueAssignFactory = $valueAssignFactory;
        $this->columnDatetimePropertyManipulator = $columnDatetimePropertyManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Move default value for entity property to constructor, the safest place', [
            new CodeSample(
                <<<'PHP'
declare(strict_types=1);

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class User
{
    /**
     * @var DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=false, options={"default"="now()"})
     */
    private $when = 'now()';
}
PHP

                ,
                <<<'PHP'
declare(strict_types=1);

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class User
{
    /**
     * @var DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $when;

    public function __construct()
    {
        $this->when = new DateTime();
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
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($node->getProperties() as $property) {
            $this->refactorProperty($property, $node);
        }

        return $node;
    }

    private function refactorProperty(Property $property, Class_ $class): ?Property
    {
        $columnTagValueNode = $this->columnDatetimePropertyAnalyzer->matchDateTimeColumnTagValueNodeInProperty(
            $property
        );

        if ($columnTagValueNode === null) {
            return null;
        }

        // 1. remove default options from database level
        $this->columnDatetimePropertyManipulator->removeDefaultOption($columnTagValueNode);

        // 2. remove default value
        $this->refactorClass($class, $property);

        // 3. remove default from property
        $onlyProperty = $property->props[0];
        $onlyProperty->default = null;

        return $property;
    }

    private function refactorClass(Class_ $class, Property $property): void
    {
        /** @var string $propertyName */
        $propertyName = $this->getName($property);
        $onlyProperty = $property->props[0];

        /** @var Expr $defaultExpr */
        $defaultExpr = $onlyProperty->default;

        $expression = $this->valueAssignFactory->createDefaultDateTimeWithValueAssign($propertyName, $defaultExpr);
        $this->constructorManipulator->addStmtToConstructor($class, $expression);
    }
}
