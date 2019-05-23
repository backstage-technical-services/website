<?php
/**
 * This file contains any configuration that is BTS specific to allow the config to be cached.
 */

return [
    'finance_db' => [
        'em_finance_url' => env('FINANCE_DB_EM_URL'),
        'url'            => env('FINANCE_DB_ADD_URL'),
        'key'            => env('FINANCE_DB_KEY'),
    ],
    'links'      => [
        'risk_assessment' => env('LINK_EVENT_RA'),
        'event_report'    => env('LINK_EVENT_REPORT'),
    ],
    'user_tz'   => 'Europe/London',
    'server_tz' => 'UTC',
];