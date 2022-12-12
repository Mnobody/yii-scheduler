<?php

declare(strict_types=1);

namespace Mnobody\Scheduler\Tests;

use Mnobody\Scheduler\Task\Task;
use Mnobody\Scheduler\ValueObject\Command;

final class TaskTest extends \PHPUnit\Framework\TestCase
{
    public function testTaskParameters(): void
    {
        $task = (new Task(new Command('command-name', ['-f']), '46 23 3,21 * *'));

        $this->assertSame('command-name', $task->getCommand()->getCommand());
        $this->assertSame(['-f'], $task->getCommand()->getParameters());
        $this->assertSame('46 23 3,21 * *', $task->getExpression());
    }

    public function testDefaultTaskExpression(): void
    {
        $task = (new Task(new Command('command-name'), '* * * * *'));

        $this->assertSame('* * * * *', $task->getExpression());
    }

    public function testWithoutOverlappingParameters(): void
    {
        $task = (new Task(new Command('command-name'), '* * * * *'))->withoutOverlappingTimeout(60);

        $this->assertTrue($task->isWithoutOverlapping());
        $this->assertSame(60, $task->getTimeout());
    }

    public function testUniqueIdGeneration(): void
    {
        $task = (new Task(new Command('command-name'), '* * * * *'));

        $this->assertSame('4a78cb8d6d', $task->getUniqueId());
    }
}
