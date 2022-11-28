<?php

declare(strict_types=1);

use Psr\Log\LogLevel;
use Mnobody\Scheduler\Command\Scheduler;
use Mnobody\Scheduler\Command\SchedulerList;

return [
    // Console commands
    'yiisoft/yii-console' => [
        'commands' => [
            'scheduler/run' => Scheduler::class,
            'scheduler/list' => SchedulerList::class,
        ],
    ],

    'mnobody/yii-scheduler' => [
        'config' => [

        ],
        'timezone' => null,
        'log' => [
            'file-target' => [
                'file' => '@runtime/logs/scheduler.log',
                'levels' => [
                    LogLevel::EMERGENCY,
                    LogLevel::ERROR,
                    LogLevel::WARNING,
                    LogLevel::INFO,
                    LogLevel::DEBUG,
                ],
                'dir-mode' => 0755,
                'file-mode' => null,
                'file-owner' => 0, // root
                'file-group' => 0, // root
            ],
            'file-rotator' => [
                'days' => 14
            ],
        ],
    ],
];
