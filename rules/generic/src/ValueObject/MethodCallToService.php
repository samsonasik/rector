<?php

declare(strict_types=1);

namespace Rector\Generic\ValueObject;

final class MethodCallToService
{
    /**
     * @var string
     */
    private $oldType;

    /**
     * @var string
     */
    private $oldMethod;

    /**
     * @var string
     */
    private $serviceType;

    public function __construct(string $oldType, string $oldMethod, string $serviceType)
    {
        $this->oldType = $oldType;
        $this->oldMethod = $oldMethod;
        $this->serviceType = $serviceType;
    }

    public function getOldType(): string
    {
        return $this->oldType;
    }

    public function getOldMethod(): string
    {
        return $this->oldMethod;
    }

    public function getServiceType(): string
    {
        return $this->serviceType;
    }
}
