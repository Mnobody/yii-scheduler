<?php

declare(strict_types=1);

namespace Mnobody\Scheduler\Event;

use Mnobody\Scheduler\Task\Task;
use Psr\EventDispatcher\StoppableEventInterface;

class SchedulerEvent implements StoppableEventInterface
{
    protected Task $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function getTask(): Task
    {
        return $this->task;
    }

    public function isPropagationStopped(): bool
    {
        return false;
    }
}
