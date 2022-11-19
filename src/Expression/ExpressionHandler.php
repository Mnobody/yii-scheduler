<?php

declare(strict_types=1);

namespace Mnobody\Scheduler\Expression;

use DateTime;
use DateTimeZone;
use DateTimeInterface;
use Cron\CronExpression;

final class ExpressionHandler
{
    private CronExpression $expression;

    private ?string $timezone;

    public function setExpression(string $expression): self
    {
        $this->expression = new CronExpression($expression);

        return $this;
    }

    public function setTimezone(?string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Determine if the Cron expression passes
     */
    public function expressionPasses(): bool
    {
        $date = (new DateTime)->setTimezone(new DateTimeZone($this->timezone ?? date_default_timezone_get()));

        return $this->expression->isDue($date);
    }

    public function getNextRunDate(string|DateTimeInterface $relativeTime = 'now'): DateTime
    {
        return $this->expression->getNextRunDate($relativeTime, 0, false, $this->timezone);
    }
}
