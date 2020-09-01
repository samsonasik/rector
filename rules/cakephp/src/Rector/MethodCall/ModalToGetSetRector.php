<?php

declare(strict_types=1);

namespace Rector\CakePHP\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\CakePHP\ValueObject\UnprefixedMethodToGetSet;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Webmozart\Assert\Assert;

/**
 * @see https://book.cakephp.org/3.0/en/appendices/3-4-migration-guide.html#deprecated-combined-get-set-methods
 * @see https://github.com/cakephp/cakephp/commit/326292688c5e6d08945a3cafa4b6ffb33e714eea#diff-e7c0f0d636ca50a0350e9be316d8b0f9
 *
 * @see \Rector\CakePHP\Tests\Rector\MethodCall\ModalToGetSetRector\ModalToGetSetRectorTest
 */
final class ModalToGetSetRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const UNPREFIXED_METHODS_TO_GET_SET = 'unprefixed_methods_to_get_set';

    /**
     * @var UnprefixedMethodToGetSet[]
     */
    private $unprefixedMethodsToGetSet = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes combined set/get `value()` to specific `getValue()` or `setValue(x)`.',
            [
                new ConfiguredCodeSample(
                    <<<'PHP'
$object = new InstanceConfigTrait;

$config = $object->config();
$config = $object->config('key');

$object->config('key', 'value');
$object->config(['key' => 'value']);
PHP
                    ,
                    <<<'PHP'
$object = new InstanceConfigTrait;

$config = $object->getConfig();
$config = $object->getConfig('key');

$object->setConfig('key', 'value');
$object->setConfig(['key' => 'value']);
PHP
                    , [
                        self::UNPREFIXED_METHODS_TO_GET_SET => [
                            new UnprefixedMethodToGetSet('InstanceConfigTrait', 'config', 'getConfig', 'setConfig'),
                        ],
                    ]
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $unprefixedMethodToGetSet = $this->matchTypeAndMethodName($node);
        if ($unprefixedMethodToGetSet === null) {
            return null;
        }

        $newName = $this->resolveNewMethodNameByCondition($node, $unprefixedMethodToGetSet);
        $node->name = new Identifier($newName);

        return $node;
    }

    public function configure(array $configuration): void
    {
        $unprefixedMethodsToGetSet = $configuration[self::UNPREFIXED_METHODS_TO_GET_SET] ?? [];
        Assert::allIsInstanceOf($unprefixedMethodsToGetSet, UnprefixedMethodToGetSet::class);
        $this->unprefixedMethodsToGetSet = $unprefixedMethodsToGetSet;
    }

    private function matchTypeAndMethodName(MethodCall $methodCall): ?UnprefixedMethodToGetSet
    {
        foreach ($this->unprefixedMethodsToGetSet as $unprefixedMethodToGetSet) {
            if (! $this->isObjectType($methodCall->var, $unprefixedMethodToGetSet->getType())) {
                continue;
            }

            if (! $this->isName($methodCall->name, $unprefixedMethodToGetSet->getUnprefixedMethod())) {
                continue;
            }

            return $unprefixedMethodToGetSet;
        }

        return null;
    }

    private function resolveNewMethodNameByCondition(
        MethodCall $methodCall,
        UnprefixedMethodToGetSet $unprefixedMethodToGetSet
    ): string {
        if (count($methodCall->args) >= $unprefixedMethodToGetSet->getMinimalSetterArgumentCount()) {
            return $unprefixedMethodToGetSet->getSetMethod();
        }

        if (! isset($methodCall->args[0])) {
            return $unprefixedMethodToGetSet->getGetMethod();
        }

        // first argument type that is considered setter
        if ($unprefixedMethodToGetSet->getFirstArgumentType() === null) {
            return $unprefixedMethodToGetSet->getGetMethod();
        }

        $firstArgumentType = $unprefixedMethodToGetSet->getFirstArgumentType();
        $argumentValue = $methodCall->args[0]->value;

        if ($firstArgumentType === 'array' && $argumentValue instanceof Array_) {
            return $unprefixedMethodToGetSet->getSetMethod();
        }

        return $unprefixedMethodToGetSet->getGetMethod();
    }
}
