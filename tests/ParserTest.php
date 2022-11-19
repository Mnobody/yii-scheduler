<?php

declare(strict_types=1);

namespace Mnobody\Scheduler\Tests;

use Mnobody\Scheduler\Expression\Parser;
use Mnobody\Scheduler\Expression\Expression;

final class ParserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideHumanReadableExpressionData
     */
    public function testCorrectParsing(string $expected, string $input): void
    {
        $result = (new Parser)
            ->setExpression(new Expression)
            ->parse($input)
            ->expression();

        self::assertSame($expected, $result);
    }

    public function provideHumanReadableExpressionData(): array
    {
        return [
            // expected, input
            ['* * * * *',  'every-minute'],
            ['*/2 * * * *', 'every-two-minutes'],
            ['*/3 * * * *', 'every-three-minutes'],
            ['*/4 * * * *', 'every-four-minutes'],
            ['*/5 * * * *', 'every-five-minutes'],
            ['*/10 * * * *', 'every-ten-minutes'],
            ['*/15 * * * *', 'every-fifteen-minutes'],
            ['0,30 * * * *', 'every-thirty-minutes'],
            ['0 * * * *', 'hourly'],
            ['13 * * * *', 'hourly-at:13'],
            ['0 */2 * * *', 'every-two-hours'],
            ['0 */3 * * *', 'every-three-hours'],
            ['0 */4 * * *', 'every-four-hours'],
            ['0 */6 * * *', 'every-six-hours'],
            ['0 0 * * *', 'daily'],
            ['0 13 * * *', 'daily-at:13:00'],
            ['28 13 * * *', 'daily-at:13:28'],
            ['0 1,13 * * *', 'twice-daily:1,13'],
            ['* * * * 1-5', 'weekdays'],
            ['* * * * 6,0', 'weekends'],
            ['* * * * 1', 'mondays'],
            ['* * * * 2', 'tuesdays'],
            ['* * * * 3', 'wednesdays'],
            ['* * * * 4', 'thursdays'],
            ['* * * * 5', 'fridays'],
            ['* * * * 6', 'saturdays'],
            ['* * * * 0', 'sundays'],
            ['0 0 * * 0', 'weekly'],
            ['0 8 * * 1', 'weekly-on:1,8:00'],
            ['58 12 * * 3', 'weekly-on:3,12:58'],
            ['0 0 1 * *', 'monthly'],
            ['0 15 4 * *', 'monthly-on:4,15:00'],
            ['34 5 31 * *', 'monthly-on:31,05:34'],
            ['0 13 1,16 * *', 'twice-monthly:1,16,13:00'],
            ['46 23 3,21 * *', 'twice-monthly:3,21,23:46'],
            ['0 14 ' . date('t') . ' * *', 'last-day-of-month:14:00'],
            ['35 15 ' . date('t') . ' * *', 'last-day-of-month:15:35'],
            ['0 0 1 1-12/3 *', 'quarterly'],
            ['0 0 1 1 *', 'yearly'],
            ['0 17 1 6 *', 'yearly-on:6,1,17:00'],
            ['45 21 13 9 *', 'yearly-on:9,13,21:45'],
            ['* * * * 1,4,6', 'days:1,4,6'],
            ['* * * * 1,2,3,4,6', 'days:1,2,3,4,6'],
            ['* * * * 6', 'days:6'],
            ['* * * * 6,0', 'days:6,0'],

            ['15 12 * * 6,0', 'daily-at:12:15;weekends'],
            ['0 * * * 3', 'hourly;wednesdays'],
            ['45 * * * 1,3,5', 'hourly-at:45;days:1,3,5'],
        ];
    }
}
