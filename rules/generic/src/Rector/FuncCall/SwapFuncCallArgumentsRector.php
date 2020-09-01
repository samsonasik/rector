<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Generic\ValueObject\FunctionArgumentSwap;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Generic\Tests\Rector\FuncCall\SwapFuncCallArgumentsRector\SwapFuncCallArgumentsRectorTest
 */
final class SwapFuncCallArgumentsRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const FUNCTION_ARGUMENT_SWAPS = 'new_argument_positions_by_function_name';

    /**
     * @var FunctionArgumentSwap[]
     */
    private $functionArgumentSwaps = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Swap arguments in function calls', [
            new ConfiguredCodeSample(
                <<<'PHP'
final class SomeClass
{
    public function run($one, $two)
    {
        return some_function($one, $two);
    }
}
PHP
                ,
                <<<'PHP'
final class SomeClass
{
    public function run($one, $two)
    {
        return some_function($two, $one);
    }
}
PHP
                , [
                    self::FUNCTION_ARGUMENT_SWAPS => [new FunctionArgumentSwap('some_function', [1, 0])],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->functionArgumentSwaps as $functionArgumentSwap) {
            if (! $this->isName($node, $functionArgumentSwap->getFunction())) {
                continue;
            }

            $newArguments = [];
            foreach ($functionArgumentSwap->getOrder() as $oldPosition => $newPosition) {
                if (! isset($node->args[$oldPosition]) || ! isset($node->args[$newPosition])) {
                    continue;
                }

                $newArguments[$newPosition] = $node->args[$oldPosition];
            }

            foreach ($newArguments as $newPosition => $argument) {
                $node->args[$newPosition] = $argument;
            }
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $functionArgumentSwaps = $configuration[self::FUNCTION_ARGUMENT_SWAPS] ?? [];
        Assert::allIsInstanceOf($functionArgumentSwaps, FunctionArgumentSwap::class);
        $this->functionArgumentSwaps = $functionArgumentSwaps;
    }
}
