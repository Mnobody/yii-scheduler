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

    public function setExpression(Expression $expression): self
    {
        $this->expression = $expression;

        return $this;
    }

    public function parse(string $schedule): Expression
    {
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
