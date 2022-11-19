<?php

declare(strict_types=1);

namespace Mnobody\Scheduler\Command;

use Throwable;
use Mnobody\Scheduler\Schedule;
use Mnobody\Scheduler\Task\Task;
use Mnobody\Scheduler\Mutex\Locker;
use Yiisoft\Yii\Console\ExitCode;
use Mnobody\Scheduler\Execute\CommandExecutor;
use Mnobody\Scheduler\Event\ScheduledTaskFailedEvent;
use Mnobody\Scheduler\Event\ScheduledTaskSkippedEvent;
use Mnobody\Scheduler\Event\ScheduledTaskStartedEvent;
use Symfony\Component\Console\Command\Command;
use Mnobody\Scheduler\Event\ScheduledTaskCompletedEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Yiisoft\Mutex\Exception\MutexLockedException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Scheduler extends Command
{
    protected static $defaultName = 'scheduler/run';
    protected static $defaultDescription = 'Scheduler Run Command';

    public Schedule $schedule;

    private Locker $locker;

    public EventDispatcherInterface $dispatcher;

    private CommandExecutor $executor;

    private bool $eventsRan = false;

    public function __construct(Schedule $schedule, Locker $locker, CommandExecutor $executor, EventDispatcherInterface $dispatcher)
    {
        $this->schedule = $schedule;
        $this->locker = $locker;
        $this->executor = $executor;
        $this->dispatcher = $dispatcher;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Task $task */
        foreach ($this->schedule->dueTasks() as $task) {

            try {

                $this->commandStartedLog($task->getCommand()->preview(), $output);

                $this->dispatcher->dispatch(new ScheduledTaskStartedEvent($task));

                if ($task->isWithoutOverlapping()) {
                    $this->locker->lock(
                        $task->getUniqueId(),
                        $task->getTimeout(),
                        function () use ($task, $output) {
                            $this->executor->execute($this->getApplication(), $task->getCommand(), $output);
                        }
                    );
                } else {
                    $this->executor->execute($this->getApplication(), $task->getCommand(), $output);
                }

                $this->dispatcher->dispatch(new ScheduledTaskCompletedEvent($task));

                $this->eventsRan = true;

            } catch (MutexLockedException $e) {
                $this->commandLockedLog($task->getCommand()->preview(), $output);
                $this->dispatcher->dispatch(new ScheduledTaskSkippedEvent($task));
            } catch (Throwable $e) {
                $this->dispatcher->dispatch(new ScheduledTaskFailedEvent($task));
            }
        }

        if (!$this->eventsRan) {
            $output->writeln('No scheduled commands are ready to run.');
        }

        return ExitCode::OK;
    }

    private function commandStartedLog(string $command, OutputInterface $output)
    {
        $dateTime = date('Y-m-d H:i:s');

        $output->writeln("[$dateTime] [$command]");
    }

    private function commandLockedLog(string $command, OutputInterface $output)
    {
        $output->writeln("Command '$command' is locked. Skipping.");
    }
}
