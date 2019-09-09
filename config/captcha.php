<?php

return [
    'secret'  => env('NOCAPTCHA_SECRET', ''),
    'sitekey' => env('NOCAPTCHA_SITEKEY', 'L'),
    'options' => [
        'timeout' => 30,
    ],
];
