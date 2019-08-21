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

    // Email recipients
    'emails' => [
        'contact' => [
            'bookings' => ['committee@bts-crew.com'],
            'enquiries' => ['committee@bts-crew.com'],
            'feedback' => ['committee@bts-crew.com'],
        ],

        'equipment' => [
            'breakage_reports' => ['equip@bts-crew.com'],
        ],

        'events' => [
            'external_accepted' => ['P.Brooks@bath.ac.uk'],
        ],

        'safety' => [
            'accident_reports' => [
                'committee@bts-crew.com',
                'safety@bts-crew.com',
                'P.Hawker@bath.ac.uk',
                'P.Brooks@bath.ac.uk ',
            ],
            'near_miss_reports' => [
                'committee@bts-crew.com',
                'safety@bts-crew.com'
            ]
        ],

        'training' => [
            'application_submitted' => ['training@bts-crew.com']
        ]
    ]
];