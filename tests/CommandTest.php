<?php

declare(strict_types=1);

namespace Mnobody\Scheduler\Tests;

use Mnobody\Scheduler\ValueObject\Command;

final class CommandTest extends \PHPUnit\Framework\TestCase
{
    public function testCommandWithoutParams(): void
    {
        $command = new Command('command-name');

        $this->assertSame('command-name', $command->getCommand());
        $this->assertSame([], $command->getParameters());
        $this->assertSame('command-name', $command->preview());
    }

    public function testCommandWithParams(): void
    {
        $command = new Command('command-name', ['-f', '-d']);

        $this->assertSame('command-name', $command->getCommand());
        $this->assertSame(['-f', '-d'], $command->getParameters());
        $this->assertSame('command-name -f -d', $command->preview());
    }
}
