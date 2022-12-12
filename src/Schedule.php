<?php

declare(strict_types=1);

namespace Mnobody\Scheduler;

use Mnobody\Scheduler\Task\Task;
use Mnobody\Scheduler\Task\Configurator;
use Mnobody\Scheduler\Expression\ExpressionHandler;
use Mnobody\Scheduler\Exception\SchedulerConfigException;

final class Schedule
{
    private ExpressionHandler $expressionHandler;

    private ?string $timezone;

    /** @var Task[]  */
    private array $tasks = [];

    /**
     * @throws SchedulerConfigException
     */
    public function __construct(Configurator $configurator, ExpressionHandler $expressionHandler, array $config = [], string $timezone = null)
    {
        $this->expressionHandler = $expressionHandler;
        $this->timezone = $timezone;

        $configurator->configure($config, $this);
    }

    public function addTask(Task $task): self
    {
        $this->tasks[$task->getUniqueId()] = $task;

        return $this;
    }

    public function dueTasks(): array
    {
        $list = [];

        foreach ($this->tasks as $task) {

            if ($this->passes($task)) {
                $list[] = $task;
            }

        }

        return $list;
    }

    public function getTasks(): array
    {
        return $this->tasks;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    private function passes(Task $task): bool
    {
        return $this->expressionHandler
            ->setExpression($task->getExpression())
            ->setTimezone($this->timezone)
            ->expressionPasses();
    }
}
