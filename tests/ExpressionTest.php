<?php

declare(strict_types=1);

namespace Mnobody\Scheduler\Tests;

use Mnobody\Scheduler\Expression\Expression;

final class ExpressionTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateWithInitialExpressionValue(): void
    {
        $this->assertSame('* * * * *', (new Expression)->expression());
    }

}
