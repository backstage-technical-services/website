<?php

use App\Helpers\MonologLogFmtFormatter;
use Monolog\Handler\StreamHandler;

return [
    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */
    'default'  => env('LOG_CHANNEL', 'stack'),
    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "custom", "stack"
    |
    */
    'channels' => [
        'stack'    => [
            'driver'   => 'stack',
            'channels' => ['monolog', 'bugsnag'],
        ],
        'monolog' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'with' => [
                'stream' => 'php://stdout',
            ],
            'formatter' => MonologLogFmtFormatter::class,
        ],
        'single'   => [
            'driver' => 'single',
            'path'   => '/var/log/app.log',
            'level'  => 'debug',
        ],
        'daily'    => [
            'driver' => 'daily',
            'path'   => '/var/log/app.log',
            'level'  => 'debug',
            'days'   => 7,
        ],
        'slack'    => [
            'driver'   => 'slack',
            'url'      => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji'    => ':boom:',
            'level'    => 'critical',
        ],
        'syslog'   => [
            'driver' => 'syslog',
            'level'  => 'debug',
        ],
        'errorlog' => [
            'driver' => 'errorlog',
            'level'  => 'debug',
        ],
        'bugsnag'  => [
            'driver' => 'bugsnag',
        ],
    ],
];
