<?php

declare(strict_types=1);

namespace Mnobody\Scheduler\Task;

use Mnobody\Scheduler\Schedule;
use Mnobody\Scheduler\Expression\Parser;
use Mnobody\Scheduler\ValueObject\Command;
use Mnobody\Scheduler\Exception\SchedulerConfigException;

final class Configurator
{
    private const PATTERN = '/((.*?) ){4}/';

    private Parser $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @throws SchedulerConfigException
     */
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
        return $this->parser->parse($schedule);
    }

    private function isRegularCronExpression(string $schedule): bool
    {
        return (bool) preg_match(self::PATTERN, $schedule);
    }

    /**
     * @throws SchedulerConfigException
     */
    private function check(array $item): void
    {
        foreach (['schedule', 'command'] as $needle) {
            if (!isset($item[$needle])) {
                throw new SchedulerConfigException(sprintf('Parameter "%s" is required', $needle));
            }
        }
    }
}
