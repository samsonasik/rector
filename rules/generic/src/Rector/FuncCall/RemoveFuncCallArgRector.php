<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Generic\ValueObject\RemovedFunctionArgument;
use Webmozart\Assert\Assert;

/**
 * @sponsor Thanks https://twitter.com/afilina & Zenika (CAN) for sponsoring this rule - visit them on https://zenika.ca/en/en
 *
 * @see \Rector\Generic\Tests\Rector\FuncCall\RemoveFuncCallArgRector\RemoveFuncCallArgRectorTest
 */
final class RemoveFuncCallArgRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const REMOVED_FUNCTION_ARGUMENTS = 'removed_function_arguments';

    /**
     * @var RemovedFunctionArgument[]
     */
    private $removedFunctionArguments = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove argument by position by function name', [
            new ConfiguredCodeSample(
<<<'CODE_SAMPLE'
remove_last_arg(1, 2);
CODE_SAMPLE
                ,
<<<'CODE_SAMPLE'
remove_last_arg(1);
CODE_SAMPLE
                , [
                    self::REMOVED_FUNCTION_ARGUMENTS => [new RemovedFunctionArgument('remove_last_arg', 1)],
                ]),
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
        foreach ($this->removedFunctionArguments as $removedFunctionArgument) {
            if (! $this->isName($node->name, $removedFunctionArgument->getFunction())) {
                continue;
            }

            foreach (array_keys($node->args) as $position) {
                if ($removedFunctionArgument->getArgumentPosition() !== $position) {
                    continue;
                }

                unset($node->args[$position]);
            }
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $removedFunctionArguments = $configuration[self::REMOVED_FUNCTION_ARGUMENTS] ?? [];
        Assert::allIsInstanceOf($removedFunctionArguments, RemovedFunctionArgument::class);
        $this->removedFunctionArguments = $removedFunctionArguments;
    }
}
