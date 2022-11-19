<?php

declare(strict_types=1);

namespace Mnobody\Scheduler\Mutex;

use Yiisoft\Mutex\Synchronizer;

final class Locker
{
    private Synchronizer $synchronizer;

    public function __construct(Synchronizer $synchronizer)
    {
        $this->synchronizer = $synchronizer;
    }

    public function lock(string $key, int $timeout, callable $callback): mixed
    {
        return $this->synchronizer->execute($key, $callback, $timeout);
    }

}
