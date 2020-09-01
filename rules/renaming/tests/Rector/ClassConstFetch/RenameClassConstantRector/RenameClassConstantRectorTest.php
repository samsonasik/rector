<?php

declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\ClassConstFetch\RenameClassConstantRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstantRector;
use Rector\Renaming\Tests\Rector\ClassConstFetch\RenameClassConstantRector\Source\DifferentClass;
use Rector\Renaming\Tests\Rector\ClassConstFetch\RenameClassConstantRector\Source\LocalFormEvents;
use Rector\Renaming\ValueObject\ClassConstantRename;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RenameClassConstantRectorTest extends AbstractRectorTestCase
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
            RenameClassConstantRector::class => [
                RenameClassConstantRector::CLASS_CONSTANT_RENAME => [
                    new ClassConstantRename(LocalFormEvents::class, 'PRE_BIND', 'PRE_SUBMIT'),
                    new ClassConstantRename(LocalFormEvents::class, 'BIND', 'SUBMIT'),
                    new ClassConstantRename(LocalFormEvents::class, 'POST_BIND', 'POST_SUBMIT'),
                    new ClassConstantRename(
                        LocalFormEvents::class,
                        'OLD_CONSTANT',
                        DifferentClass::class . '::NEW_CONSTANT'
                    ),
                ],
            ],
        ];
    }
}
