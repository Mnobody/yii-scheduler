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
    private Expression $expression;

    public function __construct(Expression $expression)
    {
        $this->expression = $expression;
    }

    public function parse(string $schedule): Expression
    {
        $this->expression->reset(); // keep initial value of cron expression

        $methods = explode(';', $schedule);

        foreach ($methods as $methodString) {

            $this->parseSingle($methodString);

        }

        return $this->expression;
    }

    private function parseSingle(string $method): void
    {
        $methodString = $method;

        $parameters = [];

        if (str_contains($method, ':')) {

            list($methodString, $parametersString) = explode(':', $method, 2);

            $parameters = $this->prepareParameters($parametersString);
        }

        $method = $this->prepareMethod($methodString);

        $this->expression->{$method}(...$parameters);
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
