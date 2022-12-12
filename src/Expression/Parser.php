<?php

declare(strict_types=1);

namespace Mnobody\Scheduler\Expression;

/**
 * Parses human-readable expression to CRON expression.
 *
 * ex. 'daily-at:12:15;hourly-at:45;weekends' is parsed to '45 12 * * 6,0'
 */
final class Parser
{
    public function parse(string $schedule): string
    {
        $expression = new Expression;

        $methods = explode(';', $schedule);

        foreach ($methods as $methodString) {

            $this->parseSingle($methodString, $expression);

        }

        return $expression->expression();
    }

    private function parseSingle(string $method, Expression $expression): void
    {
        $methodString = $method;

        $parameters = [];

        if (str_contains($method, ':')) {

            list($methodString, $parametersString) = explode(':', $method, 2);

            $parameters = $this->prepareParameters($parametersString);
        }

        $method = $this->prepareMethod($methodString);

        $expression->{$method}(...$parameters);
    }

    private function prepareMethod(string $method): string
    {
        return lcfirst(
            str_replace('-', '', ucwords($method, '-'))
        );
    }

    private function prepareParameters(string $params): array
    {
        return explode(',', $params);
    }
}
