<?php

declare(strict_types=1);

use GuzzleHttp\Cookie\SetCookie;
use Rector\Generic\Rector\FuncCall\FuncCallToMethodCallRector;
use Rector\Generic\Rector\StaticCall\StaticCallToFunctionRector;
use Rector\Generic\ValueObject\FuncNameToMethodCallName;
use Rector\Guzzle\Rector\MethodCall\MessageAsArrayRector;
use Rector\MagicDisclosure\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(
        'classes_to_defluent',
        ['GuzzleHttp\Collection', 'GuzzleHttp\Url', 'GuzzleHttp\Query', 'GuzzleHttp\Post\PostBody', SetCookie::class]
    );

    $services = $containerConfigurator->services();

    # both uses "%classes_to_defluent%
    #diff-810cdcfdd8a6b9e1fc0d1e96d7786874
    # covers https://github.com/guzzle/guzzle/commit/668209c895049759377593eed129e0949d9565b7
    $services->set(FluentChainMethodCallToNormalMethodCallRector::class)
        ->call(
            'configure',
            [[FluentChainMethodCallToNormalMethodCallRector::TYPES_TO_MATCH => '%classes_to_defluent%']]
        );

    $configuration = [
        new FuncNameToMethodCallName('GuzzleHttp\json_decode', 'GuzzleHttp\Utils', 'jsonDecode'),
        new FuncNameToMethodCallName('GuzzleHttp\get_path', 'GuzzleHttp\Utils', 'getPath'),
    ];

    $services->set(FuncCallToMethodCallRector::class)
        ->call('configure', [[
            FuncCallToMethodCallRector::FUNC_CALL_TO_CLASS_METHOD_CALL => inline_value_objects($configuration),
        ]]);

    $services->set(StaticCallToFunctionRector::class)
        ->call('configure', [[
            StaticCallToFunctionRector::STATIC_CALL_TO_FUNCTION_BY_TYPE => [
                'GuzzleHttp\Utils' => [
                    'setPath' => 'GuzzleHttp\set_path',
                ],
                'GuzzleHttp\Pool' => [
                    'batch' => 'GuzzleHttp\Pool\batch',
                ],
            ],
        ]]);

    $services->set(MessageAsArrayRector::class);

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::METHOD_CALL_RENAMES => inline_value_objects([
                new MethodCallRename('GuzzleHttp\Message\MessageInterface', 'getHeaderLines', 'getHeaderAsArray'),
            ]),
        ]]);
};
