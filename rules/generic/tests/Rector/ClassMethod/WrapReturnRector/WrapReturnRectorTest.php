<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\ClassMethod\WrapReturnRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\ClassMethod\WrapReturnRector;
use Rector\Generic\Tests\Rector\ClassMethod\WrapReturnRector\Source\SomeReturnClass;
use Rector\Generic\ValueObject\TypeMethodWrap;
use Symplify\SmartFileSystem\SmartFileInfo;

final class WrapReturnRectorTest extends AbstractRectorTestCase
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
            WrapReturnRector::class => [
                WrapReturnRector::TYPE_METHOD_WRAPS => [
                    new TypeMethodWrap(SomeReturnClass::class, 'getItem', true),
                ],
            ],
        ];
    }
}
