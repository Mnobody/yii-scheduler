<?php

declare(strict_types=1);

namespace Mnobody\Scheduler\Tests;

use Mnobody\Scheduler\Exception\SchedulerConfigException;
use Mnobody\Scheduler\Schedule;
use Mnobody\Scheduler\Task\Configurator;
use Mnobody\Scheduler\Expression\Parser;
use Mnobody\Scheduler\Expression\Expression;
use Mnobody\Scheduler\Expression\ExpressionHandler;

final class ConfiguratorTest extends \PHPUnit\Framework\TestCase
{
    protected Configurator $configurator;
    protected ExpressionHandler $expressionHandler;

    protected function setUp(): void
    {
        $this->configurator = new Configurator(new Parser(new Expression));

        $this->expressionHandler = new ExpressionHandler();
    }

    public function testEmptyConfig(): void
    {
        $schedule = new Schedule($this->configurator, $this->expressionHandler, []);

        $this->assertInstanceOf(Schedule::class, $schedule);
    }

    public function testWrongConfigNoCommand(): void
    {
        $this->expectException(SchedulerConfigException::class);
        $this->expectExceptionMessage('Parameter "command" is required');

        new Schedule($this->configurator, $this->expressionHandler, [
            [
                'schedule' => '* * * * *',
            ],
        ]);
    }

    public function testWrongConfigNoSchedule(): void
    {
        $this->expectException(SchedulerConfigException::class);
        $this->expectExceptionMessage('Parameter "schedule" is required');

        new Schedule($this->configurator, $this->expressionHandler, [
            [
                'command' => 'command-name',
            ],
        ]);
    }

    public function testCorrectConfiguration(): void
    {
        $schedule = new Schedule($this->configurator, $this->expressionHandler, [
            [
                'command' => 'hello',
                'params' => [],
                'schedule' => '* * * * *',
                'withoutOverlappingTimeout' => 60,
            ],
            [
                'command' => 'command-name',
                'params' => ['-f'],
                'schedule' => 'every-minute',
            ],
        ]);

        $this->assertArrayHasKey('f653469897', $schedule->getTasks());
        $this->assertArrayHasKey('4124fe7f84', $schedule->getTasks());

        $this->assertSame('* * * * *', $schedule->getTasks()['f653469897']->getExpression());
        $this->assertSame('* * * * *', $schedule->getTasks()['4124fe7f84']->getExpression());

        $this->assertSame('hello', $schedule->getTasks()['f653469897']->getCommand()->preview());
        $this->assertSame('command-name -f', $schedule->getTasks()['4124fe7f84']->getCommand()->preview());

        $this->assertTrue($schedule->getTasks()['f653469897']->isWithoutOverlapping());
        $this->assertFalse($schedule->getTasks()['4124fe7f84']->isWithoutOverlapping());

        $this->assertSame(60, $schedule->getTasks()['f653469897']->getTimeout());
        $this->assertSame(0, $schedule->getTasks()['4124fe7f84']->getTimeout());
    }
}
