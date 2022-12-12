<?php

declare(strict_types=1);

namespace Mnobody\Scheduler\Task;

use Mnobody\Scheduler\ValueObject\Command;

final class Task
{
    /** Timeout in seconds */
    private const DEFAULT_TIMEOUT = 0;

    private Command $command;

    /** The cron expression representing the task's frequency. */
    private string $expression;

    /** Indicates if the command should not overlap itself. */
    private bool $withoutOverlapping = false;

    private int $timeout = self::DEFAULT_TIMEOUT;

    public function __construct(Command $command, string $expression)
    {
        $this->command = $command;
        $this->expression = $expression;
    }

    public function getCommand(): Command
    {
        return $this->command;
    }

    public function getExpression(): string
    {
        return $this->expression;
    }

    public function isWithoutOverlapping(): bool
    {
        return $this->withoutOverlapping;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Do not allow the command to overlap itself.
     */
    public function withoutOverlappingTimeout(int $timeout = self::DEFAULT_TIMEOUT): self
    {
        $this->withoutOverlapping = true;

        $this->timeout = $timeout;

        return $this;
    }

    public function getUniqueId(): string
    {
        return substr(sha1($this->expression . $this->command->preview()), 0, 10);
    }
}
