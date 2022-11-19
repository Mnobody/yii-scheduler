<?php

declare(strict_types=1);

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

//    'mnobody/yii-scheduler' => [
//        'config' => [
//
//        ],
//        'timezone' => null,
//    ],
];
