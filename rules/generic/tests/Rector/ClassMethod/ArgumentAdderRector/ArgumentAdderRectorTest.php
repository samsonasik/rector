<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\ClassMethod\ArgumentAdderRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Generic\Tests\Rector\ClassMethod\ArgumentAdderRector\Source\SomeContainerBuilder;
use Rector\Generic\Tests\Rector\ClassMethod\ArgumentAdderRector\Source\SomeParentClient;
use Rector\Generic\ValueObject\AddedArgument;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ArgumentAdderRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ArgumentAdderRector::class => [
                ArgumentAdderRector::ADDED_ARGUMENTS => [
                    new AddedArgument(SomeContainerBuilder::class, 'compile', 0, 'isCompiled', false),
                    new AddedArgument(SomeContainerBuilder::class, 'addCompilerPass', 2, 'priority', 0, 'int'),

                    // scoped
                    new AddedArgument(
                        SomeParentClient::class,
                        'submit',
                        2,
                        'serverParameters',
                        [],
                        'array',
                        ArgumentAdderRector::SCOPE_PARENT_CALL
                    ),
                    new AddedArgument(
                        SomeParentClient::class,
                        'submit',
                        2,
                        'serverParameters',
                        [],
                        'array',
                        ArgumentAdderRector::SCOPE_CLASS_METHOD
                    ),
                ],
            ],
        ];
    }
}
