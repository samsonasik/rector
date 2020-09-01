<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\ClassMethod\ArgumentDefaultValueReplacerRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\ClassMethod\ArgumentDefaultValueReplacerRector;
use Rector\Generic\ValueObject\ReplacedArgument;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ArgumentDefaultValueReplacerRectorTest extends AbstractRectorTestCase
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
            ArgumentDefaultValueReplacerRector::class => [
                ArgumentDefaultValueReplacerRector::REPLACED_ARGUMENTS => [
                    new ReplacedArgument(
                        'Symfony\Component\DependencyInjection\Definition',
                        'setScope',
                        0,
                        'Symfony\Component\DependencyInjection\ContainerBuilder::SCOPE_PROTOTYPE',
                                    false
                    ),

                    new ReplacedArgument('Symfony\Component\Yaml\Yaml', 'parse', 1, [
                        false,
                        false,
                        true,
                    ], 'Symfony\Component\Yaml\Yaml::PARSE_OBJECT_FOR_MAP'),

                    new ReplacedArgument('Symfony\Component\Yaml\Yaml', 'parse', 1, [
                        false,
                        true,
                    ], 'Symfony\Component\Yaml\Yaml::PARSE_OBJECT'),
                    new ReplacedArgument('Symfony\Component\Yaml\Yaml', 'parse', 1, false, 0),
                    new ReplacedArgument(
                        'Symfony\Component\Yaml\Yaml',
                        'parse',
                        1,
                        true,
                        'Symfony\Component\Yaml\Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE'
                    ),
                    new ReplacedArgument('Symfony\Component\Yaml\Yaml', 'dump', 3, [
                        false,
                        true,
                    ], 'Symfony\Component\Yaml\Yaml::DUMP_OBJECT'),
                    new ReplacedArgument(
                        'Symfony\Component\Yaml\Yaml',
                        'dump',
                        3,
                        true,
                        'Symfony\Component\Yaml\Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE'
                    ),
                ],
            ],
        ];
    }
}
