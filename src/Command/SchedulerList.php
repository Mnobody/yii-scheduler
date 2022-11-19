<?php

declare(strict_types=1);

namespace Mnobody\Scheduler\Command;

use Mnobody\Scheduler\Expression\ExpressionHandler;
use Mnobody\Scheduler\Schedule;
use Mnobody\Scheduler\Task\Task;
use Yiisoft\Yii\Console\ExitCode;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class SchedulerList extends Command
{
    private const DATETIME_FORMAT = 'Y-m-d H:i:s';

    protected static $defaultName = 'scheduler/list';
    protected static $defaultDescription = 'Scheduler List Commands';

    private Schedule $schedule;
    private ExpressionHandler $expressionHandler;

    public function __construct(Schedule $schedule, ExpressionHandler $expressionHandler)
    {
        $this->schedule = $schedule;
        $this->expressionHandler = $expressionHandler;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = new Table($output);
        $table->setHeaders([
            'ID',
            'Command',
            'Expression',
            'Next Execution',
            'Without overlapping',
        ]);

        /** @var Task $task */
        foreach ($this->schedule->getTasks() as $task) {
            $table->addRow([
                $task->getUniqueId(),
                $task->getCommand()->preview(),
                $task->getExpression(),
                $this->getNextRunDate($task),
                $task->isWithoutOverlapping() ? 'yes' : 'no',
            ]);
        }
        $table->render();

        return ExitCode::OK;
    }

    private function getNextRunDate(Task $task): string
    {
        return $this->expressionHandler
            ->setExpression($task->getExpression())
            ->setTimezone($this->schedule->getTimezone())
            ->getNextRunDate()
            ->format(self::DATETIME_FORMAT);
    }
}
