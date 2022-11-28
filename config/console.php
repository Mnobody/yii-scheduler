<?php

declare(strict_types=1);

use Yiisoft\Log\Logger;
use Yiisoft\Aliases\Aliases;
use Mnobody\Scheduler\Schedule;
use Yiisoft\Definitions\Reference;
use Mnobody\Scheduler\Mutex\Locker;
use Mnobody\Scheduler\Task\Configurator;
use Mnobody\Scheduler\Command\Scheduler;
use Yiisoft\Definitions\DynamicReference;
use Mnobody\Scheduler\Log\DailyFileRotator;
use Mnobody\Scheduler\Log\SchedulerFileTarget;
use Mnobody\Scheduler\Execute\CommandExecutor;
use Psr\EventDispatcher\EventDispatcherInterface;
use Mnobody\Scheduler\Expression\ExpressionHandler;
use Mnobody\Scheduler\Log\SchedulerFileRotatorInterface;

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

    SchedulerFileRotatorInterface::class => [
        'class' => DailyFileRotator::class,
        '__construct()' => [
            $params['mnobody/yii-scheduler']['log']['file-rotator']['days'],
        ],
    ],

    Scheduler::class => [
        'class' => Scheduler::class,
        '__construct()' => [
            'schedule' => Reference::to(Schedule::class),
            'locker' => Reference::to(Locker::class),
            'executor' => Reference::to(CommandExecutor::class),
            'dispatcher' => Reference::to(EventDispatcherInterface::class),
            'logger' => DynamicReference::to([
                'class' => Logger::class,
                '__construct()' => [
                    'targets' => [
                        DynamicReference::to(
                            static function (Aliases $aliases, SchedulerFileRotatorInterface $fileRotator) use ($params) {
                                return (new SchedulerFileTarget(
                                    $aliases->get($params['mnobody/yii-scheduler']['log']['file-target']['file']),
                                    $fileRotator,
                                    $params['mnobody/yii-scheduler']['log']['file-target']['dir-mode'],
                                    $params['mnobody/yii-scheduler']['log']['file-target']['file-mode'],
                                    $params['mnobody/yii-scheduler']['log']['file-target']['file-owner'],
                                    $params['mnobody/yii-scheduler']['log']['file-target']['file-group'],
                                ))
                                    ->setLevels($params['mnobody/yii-scheduler']['log']['file-target']['levels']);
                            }
                        )
                    ],
                ],
            ]),
        ],
    ],
];
