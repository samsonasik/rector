<?php

declare(strict_types=1);

namespace Rector\Generic\ValueObject;

final class MethodCallRenameWithAddedArguments
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $oldMethod;

    /**
     * @var string
     */
    private $newMethod;

    /**
     * @var mixed[]
     */
    private $newArguments = [];

    public function __construct(string $type, string $oldMethod, string $newMethod, array $newArguments)
    {
        $this->type = $type;
        $this->oldMethod = $oldMethod;
        $this->newMethod = $newMethod;
        $this->newArguments = $newArguments;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getOldMethod(): string
    {
        return $this->oldMethod;
    }

    public function getNewMethod(): string
    {
        return $this->newMethod;
    }

    public function getNewArguments(): array
    {
        return $this->newArguments;
    }
}
