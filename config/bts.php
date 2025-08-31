<?php
/**
 * This file contains any configuration that is BTS specific to allow the config to be cached.
 */

return [
    'analytics_code' => env('GOOGLE_ANALYTICS_CODE', ''),

    'finance_db' => [
        'em_finance_url' => env('FINANCE_DB_EM_URL'),
        'url' => env('FINANCE_DB_ADD_URL'),
        'key' => env('FINANCE_DB_KEY'),
    ],
    'links' => [
        'risk_assessment' => env('LINK_EVENT_RA'),
        'event_report' => env('LINK_EVENT_REPORT'),
        'instagram' => 'https://www.instagram.com/backstageatbath/',
        'wiki' => 'https://wiki.bts-crew.com',
        'pc_deployment' => 'https://pc-deployment.bts-crew.com',
        'network_management' => 'https://librenms.bts-crew.com',
        'skills_matrix' => 'https://docs.google.com/spreadsheets/d/1Xq3JVDMIE52_0iWZiymMsDvMINEfz0p0G0x2WdImSHo',
        'telephony' => 'https://telephony.bts-crew.com',
    ],

    // Emails
    'emails' => [
        'account' => [
            'created' => ['sec@bts-crew.com'],
        ],

        'contact' => [
            'bookings' => ['committee@bts-crew.com'],
            'booking_receipt' => ['pm@bts-crew.com'],
            'enquiries' => ['committee@bts-crew.com'],
            'enquiry_receipt' => ['committee@bts-crew.com'],
            'feedback' => ['committee@bts-crew.com'],
        ],

        'equipment' => [
            'breakage_reports' => ['equip@bts-crew.com'],
        ],

        'events' => [
            'accepted_external' => [
                'from' => ['pm@bts-crew.com'],
                'to' => ['cjl25@bath.ac.uk'],
                'cc' => ['committee@bts-crew.com'],
                'reply' => ['committee@bts-crew.com'],
            ],
            'volunteered' => ['pm@bts-crew.com'],
        ],

        'finance' => ['treas@bts-crew.com'],

        'safety' => [
            'accident_reports' => [
                'committee@bts-crew.com',
                'safety@bts-crew.com',
                'P.Hawker@bath.ac.uk',
                'su-healthandsafety@bath.ac.uk',
            ],
            'accident_receipt' => ['safety@bts-crew.com'],
            'near_miss_reports' => ['committee@bts-crew.com', 'safety@bts-crew.com', 'su-healthandsafety@bath.ac.uk'],
        ],

        'training' => [
            'application_submitted' => ['training@bts-crew.com'],
            'application_processed' => ['training@bts-crew.com'],
        ],
    ],
];
