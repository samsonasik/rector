<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Generic\ValueObject\TypeToStaticCall;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Generic\Tests\Rector\New_\NewToStaticCallRector\NewToStaticCallRectorTest
 */
final class NewToStaticCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const TYPE_TO_STATIC_CALLS = 'type_to_static_calls';

    /**
     * @var TypeToStaticCall[]
     */
    private $typeToStaticCalls = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change new Object to static call', [
            new ConfiguredCodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        new Cookie($name);
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        Cookie::create($name);
    }
}
PHP
                ,
                [
                    self::TYPE_TO_STATIC_CALLS => [new TypeToStaticCall('Cookie', 'Cookie', 'create')],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [New_::class];
    }

    /**
     * @param New_ $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->typeToStaticCalls as $typeToStaticCall) {
            if (! $this->isObjectType($node->class, $typeToStaticCall->getType())) {
                continue;
            }

            return $this->createStaticCall(
                $typeToStaticCall->getStaticCallClass(),
                $typeToStaticCall->getStaticCallMethod(),
                $node->args
            );
        }

        return null;
    }

    public function configure(array $configuration): void
    {
        $typeToStaticCalls = $configuration[self::TYPE_TO_STATIC_CALLS] ?? [];
        Assert::allIsInstanceOf($typeToStaticCalls, TypeToStaticCall::class);
        $this->typeToStaticCalls = $typeToStaticCalls;
    }
}
