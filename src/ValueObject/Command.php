<?php

declare(strict_types=1);

namespace Mnobody\Scheduler\ValueObject;

final class Command
{
    private string $command;

    private array $parameters;

    public function __construct(string $command, array $parameters = [])
    {
        $this->command = $command;
        $this->parameters = $parameters;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function preview(): string
    {
        return implode(
            ' ',
            array_merge([$this->getCommand()], $this->getParameters())
        );
    }
}
