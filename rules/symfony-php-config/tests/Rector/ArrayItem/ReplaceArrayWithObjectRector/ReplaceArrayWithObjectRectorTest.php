<?php

declare(strict_types=1);

namespace Rector\SymfonyPhpConfig\Tests\Rector\ArrayItem\ReplaceArrayWithObjectRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\SymfonyPhpConfig\Rector\ArrayItem\ReplaceArrayWithObjectRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ReplaceArrayWithObjectRectorTest extends AbstractRectorTestCase
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
            ReplaceArrayWithObjectRector::class => [
                ReplaceArrayWithObjectRector::CONSTANT_NAMES_TO_VALUE_OBJECTS => [
                    'Rector\Renaming\Rector\MethodCall\RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS' => MethodCallRename::class,
                ],
            ],
        ];
    }
}
