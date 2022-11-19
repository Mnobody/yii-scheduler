<?php

declare(strict_types=1);

namespace Mnobody\Scheduler\Task;

use Mnobody\Scheduler\Schedule;
use Mnobody\Scheduler\Expression\Parser;
use Mnobody\Scheduler\ValueObject\Command;
use Mnobody\Scheduler\Expression\Expression;
use Mnobody\Scheduler\Exception\SchedulerConfigException;

final class Configurator
{
    private const PATTERN = '/((.*?) ){4}/';

    private Parser $parser;
    private Expression $expression;

    public function __construct(Parser $parser, Expression $expression)
    {
        $this->parser = $parser;
        $this->expression = $expression;
    }

    public function configure(array $config, Schedule $schedule): void
    {
        foreach ($config as $item) {

            $this->check($item);

            $expression = $this->isRegularCronExpression($item['schedule'])
                ? $item['schedule']
                : $this->parse($item['schedule']);;

            $task = new Task(
                new Command($item['command'], $item['params'] ?? []),
                $expression
            );

            if (isset($item['withoutOverlappingTimeout'])) {
                $task->withoutOverlappingTimeout($item['withoutOverlappingTimeout']);
            }

            $schedule->addTask($task);
        }
    }

    private function parse(string $schedule): string
    {
        return $this
            ->parser
            ->setExpression(clone $this->expression) // keep initial value of cron expression
            ->parse($schedule)
            ->expression();
    }

    private function isRegularCronExpression(string $schedule): bool
    {
        return (bool) preg_match(self::PATTERN, $schedule);
    }

    private function check(array $item): void
    {
        if (!isset($item['schedule'])) {
            throw new SchedulerConfigException('Parameter "schedule" is required');
        }

        if (!isset($item['command'])) {
            throw new SchedulerConfigException('Parameter "command" is required');
        }
    }
}
