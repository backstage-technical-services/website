<?php
return [
    'client_id'     => env('GOOGLE_CLIENT_ID', null),
    'client_secret' => env('GOOGLE_CLIENT_SECRET', null),
    'refresh_uri'   => env('GOOGLE_REFRESH_URL', null),
    'refresh_token' => env('GOOGLE_REFRESH_TOKEN', null),
    'token_path'    => storage_path('google_token.json'),
    'scopes'        => [
        \Google_Service_Drive::DRIVE,
    ],
];