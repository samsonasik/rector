<?php

declare(strict_types=1);

namespace Rector\CakePHP\Tests\Rector\MethodCall\RenameMethodCallBasedOnParameterRector;

use Iterator;
use Rector\CakePHP\Rector\MethodCall\RenameMethodCallBasedOnParameterRector;
use Rector\CakePHP\Tests\Rector\MethodCall\RenameMethodCallBasedOnParameterRector\Source\SomeModelType;
use Rector\CakePHP\ValueObject\CallWithParamRename;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RenameMethodCallBasedOnParameterRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return Iterator
     */
    public function provideData(): iterable
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RenameMethodCallBasedOnParameterRector::class => [
                RenameMethodCallBasedOnParameterRector::CALLS_WITH_PARAM_RENAMES => [
                    new CallWithParamRename(SomeModelType::class, 'getParam', 'paging', 'getAttribute'),
                    new CallWithParamRename(SomeModelType::class, 'withParam', 'paging', 'withAttribute'),
                ],
            ],
        ];
    }
}
