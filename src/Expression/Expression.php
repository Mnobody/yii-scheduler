<?php

declare(strict_types=1);

namespace Mnobody\Scheduler\Expression;

final class Expression
{
    private const SUNDAY = 0;
    private const MONDAY = 1;
    private const TUESDAY = 2;
    private const WEDNESDAY = 3;
    private const THURSDAY = 4;
    private const FRIDAY = 5;
    private const SATURDAY = 6;

    /**
     * The cron expression representing the command's frequency.
     */
    private string $expression;

    public function __construct(string $expression = '* * * * *')
    {
        $this->expression = $expression;
    }

    public function expression(): string
    {
        return $this->expression;
    }

    /**
     * Schedule the task to run every minute.
     */
    public function everyMinute(): self
    {
        return $this->spliceIntoPosition(1, '*');
    }

    /**
     * Schedule the task to run every two minutes.
     */
    public function everyTwoMinutes(): self
    {
        return $this->spliceIntoPosition(1, '*/2');
    }

    /**
     * Schedule the task to run every three minutes.
     */
    public function everyThreeMinutes(): self
    {
        return $this->spliceIntoPosition(1, '*/3');
    }

    /**
     * Schedule the task to run every four minutes.
     */
    public function everyFourMinutes(): self
    {
        return $this->spliceIntoPosition(1, '*/4');
    }

    /**
     * Schedule the task to run every five minutes.
     */
    public function everyFiveMinutes(): self
    {
        return $this->spliceIntoPosition(1, '*/5');
    }

    /**
     * Schedule the task to run every ten minutes.
     */
    public function everyTenMinutes(): self
    {
        return $this->spliceIntoPosition(1, '*/10');
    }

    /**
     * Schedule the task to run every fifteen minutes.
     */
    public function everyFifteenMinutes(): self
    {
        return $this->spliceIntoPosition(1, '*/15');
    }

    /**
     * Schedule the task to run every thirty minutes.
     */
    public function everyThirtyMinutes(): self
    {
        return $this->spliceIntoPosition(1, '0,30');
    }

    /**
     * Schedule the task to run hourly.
     */
    public function hourly(): self
    {
        return $this->spliceIntoPosition(1, 0);
    }

    /**
     * Schedule the task to run hourly at a given offset in the hour.
     */
    public function hourlyAt(array|int|string $offset): self
    {
        $offset = is_array($offset) ? implode(',', $offset) : $offset;

        return $this->spliceIntoPosition(1, $offset);
    }

    /**
     * Schedule the task to run every two hours.
     */
    public function everyTwoHours(): self
    {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, '*/2');
    }

    /**
     * Schedule the task to run every three hours.
     */
    public function everyThreeHours(): self
    {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, '*/3');
    }

    /**
     * Schedule the task to run every four hours.
     */
    public function everyFourHours(): self
    {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, '*/4');
    }

    /**
     * Schedule the task to run every six hours.
     */
    public function everySixHours(): self
    {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, '*/6');
    }

    /**
     * Schedule the task to run daily.
     */
    public function daily(): self
    {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0);
    }

    /**
     * Schedule the task to run daily at a given time (10:00, 19:30, etc).
     */
    public function dailyAt(string $time): self
    {
        $segments = explode(':', $time);

        return $this->spliceIntoPosition(2, (int) $segments[0])
            ->spliceIntoPosition(1, count($segments) === 2 ? (int) $segments[1] : '0');
    }

    /**
     * Schedule the task to run twice daily.
     */
    public function twiceDaily(int|string $first = 1, int|string $second = 13): self
    {
        $hours = $first.','.$second;

        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, $hours);
    }

    /**
     * Schedule the task to run only on weekdays.
     */
    public function weekdays(): self
    {
        return $this->days(self::MONDAY . '-' . self::FRIDAY);
    }

    /**
     * Schedule the task to run only on weekends.
     */
    public function weekends(): self
    {
        return $this->days(self::SATURDAY . ',' . self::SUNDAY);
    }

    /**
     * Schedule the task to run only on Mondays.
     */
    public function mondays(): self
    {
        return $this->days(self::MONDAY);
    }

    /**
     * Schedule the task to run only on Tuesdays.
     */
    public function tuesdays(): self
    {
        return $this->days(self::TUESDAY);
    }

    /**
     * Schedule the task to run only on Wednesdays.
     */
    public function wednesdays(): self
    {
        return $this->days(self::WEDNESDAY);
    }

    /**
     * Schedule the task to run only on Thursdays.
     */
    public function thursdays(): self
    {
        return $this->days(self::THURSDAY);
    }

    /**
     * Schedule the task to run only on Fridays.
     */
    public function fridays(): self
    {
        return $this->days(self::FRIDAY);
    }

    /**
     * Schedule the task to run only on Saturdays.
     */
    public function saturdays(): self
    {
        return $this->days(self::SATURDAY);
    }

    /**
     * Schedule the task to run only on Sundays.
     */
    public function sundays(): self
    {
        return $this->days(self::SUNDAY);
    }

    /**
     * Schedule the task to run weekly.
     */
    public function weekly(): self
    {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0)
            ->spliceIntoPosition(5, 0);
    }

    /**
     * Schedule the task to run weekly on a given day and time.
     */
    public function weeklyOn(int|string $dayOfWeek, string $time = '0:0'): self
    {
        $this->dailyAt($time);

        return $this->days($dayOfWeek);
    }

    /**
     * Schedule the task to run monthly.
     */
    public function monthly(): self
    {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0)
            ->spliceIntoPosition(3, 1);
    }

    /**
     * Schedule the task to run monthly on a given day and time.
     */
    public function monthlyOn(int|string $dayOfMonth = 1, string $time = '0:0'): self
    {
        $this->dailyAt($time);

        return $this->spliceIntoPosition(3, $dayOfMonth);
    }

    /**
     * Schedule the task to run twice monthly at a given time.
     */
    public function twiceMonthly(int|string $first = 1, int|string $second = 16, string $time = '0:0'): self
    {
        $daysOfMonth = $first.','.$second;

        $this->dailyAt($time);

        return $this->spliceIntoPosition(3, $daysOfMonth);
    }

    /**
     * Schedule the task to run on the last day of the month.
     */
    public function lastDayOfMonth(string $time = '0:0'): self
    {
        $this->dailyAt($time);

        return $this->spliceIntoPosition(3, date('t'));
    }

    /**
     * Schedule the task to run quarterly.
     */
    public function quarterly(): self
    {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0)
            ->spliceIntoPosition(3, 1)
            ->spliceIntoPosition(4, '1-12/3');
    }

    /**
     * Schedule the task to run yearly.
     */
    public function yearly(): self
    {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0)
            ->spliceIntoPosition(3, 1)
            ->spliceIntoPosition(4, 1);
    }

    /**
     * Schedule the task to run yearly on a given month, day, and time.
     */
    public function yearlyOn(int|string $month = 1, int|string $dayOfMonth = 1, string $time = '0:0'): self
    {
        $this->dailyAt($time);

        return $this->spliceIntoPosition(3, $dayOfMonth)
            ->spliceIntoPosition(4, $month);
    }

    /**
     * Set the days of the week the command should run on.
     */
    public function days(mixed $days): self
    {
        $days = is_array($days) ? $days : func_get_args();

        return $this->spliceIntoPosition(5, implode(',', $days));
    }

    /**
     * Splice the given value into the given position of the expression.
     */
    protected function spliceIntoPosition(int $position, int|string $value): self
    {
        $segments = explode(' ', $this->expression);

        $segments[$position - 1] = $value;

        $this->expression = implode(' ', $segments);

        return $this;
    }
}
