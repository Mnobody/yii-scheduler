# Yii3 Scheduler

Scheduler package, inspired by Laravel scheduler.

## Requirements

- PHP 8.0 or higher.

## Installation

The package could be installed with composer.json:

```json
{
  "require": {
    "mnobody/yii-scheduler": "@dev"
  },
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/mnobody/yii-scheduler"
    }
  ]
} 
```

## Configuration

### General usage

Configure the command list in your project's params.php file

```php

return [
    
    // ...
    
    'mnobody/yii-scheduler' => [
        'config' => [
            [
                'command' => 'hello',
                'params' => [],
                'schedule' => '* * * * *',
                'withoutOverlappingTimeout' => 45,
            ],
            [
                'command' => 'hello2',
                'params' => [],
                'schedule' => 'hourly-at:45;weekends',
            ],
            [
                'command' => 'hello3',
                'params' => ['-f', '-d'],
                'schedule' => 'every-three-hours',
            ],
            [
                'command' => 'hello4',
                'schedule' => 'days:1,3,5',
            ],
        ],
        'timezone' => null, // 'UTC', 'Europe/Warsaw'
    ],
];
```

### Logging
By default, logging with daily rotation of log files is enabled
```php

return [
    
    // ...
    
    'mnobody/yii-scheduler' => [
        'config' => [ ... ],
        'timezone' => null, // 'UTC', 'Europe/Warsaw'
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
```

You can disable logging by injecting a NullLogger instance instead of the default logger
```php
Scheduler::class => [
        'class' => Scheduler::class,
        '__construct()' => [
            'schedule' => Reference::to(Schedule::class),
            'locker' => Reference::to(Locker::class),
            'executor' => Reference::to(CommandExecutor::class),
            'dispatcher' => Reference::to(EventDispatcherInterface::class),
            'logger' => Reference::to(\Psr\Log\NullLogger::class), // if you want to disable logging
        ],
    ],
```

### Mutex

Ex. for FileMutex package
```php
MutexFactoryInterface::class => [
    'class' => FileMutexFactory::class,
    '__construct()' => [
        'mutexPath' => 'runtime/lock',
    ],
],
```

### CRON Configuration
Add the command below in the CRONTAB configuration
```text
* * * * * php yii scheduler/run >> /dev/null 2>&1
```

### Command list
```text
php yii scheduler/list
```

### Command Configuration
As the second argument of the service is provided list of commands

Field _command_ is __required__ \
Fields _schedule_ is __required__

You can provide regular CRON expression, or human-readable expression \
Possible _schedule_ human-readable expressions:

|                     |                    |                           |                          |
|---------------------|--------------------|---------------------------|--------------------------|
| every-minute        | every-two-minutes  | every-three-minutes       | every-four-minutes       |
| every-five-minutes  | every-ten-minutes  | every-fifteen-minutes     | every-thirty-minutes     |
| hourly              | hourly-at:13       | every-two-hours           | every-three-hours        |
| every-four-hours    | every-six-hours    | daily                     | daily-at:13:00           |
| twice-daily:1,13    | weekdays           | weekends                  | mondays                  |
| tuesdays            | wednesdays         | thursdays                 | fridays                  |
| saturdays           | sundays            | weekly                    | weekly-on:1,8:00         |
| monthly             | monthly-on:4,15:00 | twice-monthly:1,16,13:00  | last-day-of-month:14:00  |
| quarterly           | yearly             | yearly-on:6,1,17:00       | days:1,4,6               |

Also _schedule_ expressions can be combined:

|                         |                   |                         |
|-------------------------|-------------------|-------------------------|
| daily-at:12:15;weekends | hourly;wednesdays | hourly-at:45;days:1,3,5 |

### Overlapping
To prevent overlapping provide _withoutOverlappingTimeout_ param with a number of seconds that specifies the lock lifetime \
Example: if the specified time is less than 60 seconds and the command takes longer to execute (and cron hits the command every minute), the command will be skipped the next time it is run

### Timezone
As the third argument of the service _timezone_ can be provided. If it is missing, the default system timezone will be used

## Testing

### Unit testing

The package is tested with [PHPUnit](https://phpunit.de/). To run tests:

```shell
./vendor/bin/phpunit
```
