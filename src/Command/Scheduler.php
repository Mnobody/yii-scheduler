<?php

declare(strict_types=1);

namespace Mnobody\Scheduler\Command;

use Throwable;
use Psr\Log\LoggerInterface;
use Mnobody\Scheduler\Schedule;
use Mnobody\Scheduler\Task\Task;
use Yiisoft\Yii\Console\ExitCode;
use Mnobody\Scheduler\Mutex\Locker;
use Mnobody\Scheduler\Execute\CommandExecutor;
use Symfony\Component\Console\Command\Command;
use Psr\EventDispatcher\EventDispatcherInterface;
use Yiisoft\Mutex\Exception\MutexLockedException;
use Symfony\Component\Console\Input\InputInterface;
use Yiisoft\Yii\Console\Output\ConsoleBufferedOutput;
use Mnobody\Scheduler\Event\ScheduledTaskFailedEvent;
use Symfony\Component\Console\Output\OutputInterface;
use Mnobody\Scheduler\Event\ScheduledTaskSkippedEvent;
use Mnobody\Scheduler\Event\ScheduledTaskStartedEvent;
use Mnobody\Scheduler\Event\ScheduledTaskCompletedEvent;

final class Scheduler extends Command
{
    protected static $defaultName = 'scheduler/run';
    protected static $defaultDescription = 'Scheduler Run Command';

    public Schedule $schedule;

    private Locker $locker;

    private CommandExecutor $executor;

    public EventDispatcherInterface $dispatcher;

    private LoggerInterface $logger;

    private bool $eventsRan = false;

    public function __construct(Schedule $schedule, Locker $locker, CommandExecutor $executor, EventDispatcherInterface $dispatcher, LoggerInterface $logger)
    {
        $this->schedule = $schedule;
        $this->locker = $locker;
        $this->executor = $executor;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;

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
                $output->writeln('Exception: ' . $e->getMessage());
                $this->dispatcher->dispatch(new ScheduledTaskFailedEvent($task));
            }
        }

        if (!$this->eventsRan) {
            $output->writeln('No scheduled commands are ready to run.');
        }

        if ($output instanceof ConsoleBufferedOutput) {
            $this->logger->info($output->fetch());
        }

        return ExitCode::OK;
    }

    private function commandStartedLog(string $command, OutputInterface $output)
    {
        $dateTime = date('Y-m-d H:i:s');

        $output->writeln("[$dateTime] [$command] Running!");
    }

    private function commandLockedLog(string $command, OutputInterface $output)
    {
        $output->writeln("Command '$command' is locked. Skipping.");
    }
}
