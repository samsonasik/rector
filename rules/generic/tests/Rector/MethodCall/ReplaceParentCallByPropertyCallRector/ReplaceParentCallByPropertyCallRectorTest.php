<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\MethodCall\ReplaceParentCallByPropertyCallRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\MethodCall\ReplaceParentCallByPropertyCallRector;
use Rector\Generic\Tests\Rector\MethodCall\ReplaceParentCallByPropertyCallRector\Source\TypeClassToReplaceMethodCallBy;
use Rector\Generic\ValueObject\ParentCallToProperty;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ReplaceParentCallByPropertyCallRectorTest extends AbstractRectorTestCase
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

    protected function getRectorsWithConfiguration(): array
    {
        return [
            ReplaceParentCallByPropertyCallRector::class => [
                ReplaceParentCallByPropertyCallRector::PARENT_CALLS_TO_PROPERTIES => [
                    new ParentCallToProperty(TypeClassToReplaceMethodCallBy::class, 'someMethod', 'someProperty'),
                ],
            ],
        ];
    }
}
