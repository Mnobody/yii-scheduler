<?php

declare(strict_types=1);

namespace Mnobody\Scheduler\Tests;

use Mnobody\Scheduler\Expression\ExpressionHandler;

final class ExpressionHandlerTest extends \PHPUnit\Framework\TestCase
{
    public function testCorrectNextRunDate(): void
    {
        $handler = (new ExpressionHandler)
            ->setExpression('0 0 1 1 *') // At 00:00 on day-of-month 1 in January.
            ->setTimezone('UTC');

        $this->assertSame('2023-01-01 00:00:00', $handler->getNextRunDate('2022-11-13')->format('Y-m-d H:i:s'));
    }

    public function testExpressionPasses(): void
    {
        $handler = (new ExpressionHandler)
            ->setExpression('* * * * *') // every minute
            ->setTimezone('UTC');

        $this->assertTrue($handler->expressionPasses());
    }
}
