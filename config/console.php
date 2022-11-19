<?php

declare(strict_types=1);

use Mnobody\Scheduler\Schedule;
use Yiisoft\Definitions\Reference;
use Mnobody\Scheduler\Task\Configurator;
use Mnobody\Scheduler\Expression\ExpressionHandler;

/**
 * @var array $params
 */

return [
    Schedule::class => [
        'class' => Schedule::class,
        '__construct()' => [
            'configurator' => Reference::to(Configurator::class),
            'expressionHandler' => Reference::to(ExpressionHandler::class),
            'config' => $params['mnobody/yii-scheduler']['config'],
            'timezone' => $params['mnobody/yii-scheduler']['timezone'],
        ],
    ],
];
