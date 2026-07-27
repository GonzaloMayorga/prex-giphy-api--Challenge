<?php

declare(strict_types=1);

return [
    'base_url' => env(
        'GIPHY_BASE_URL',
        'https://api.giphy.com/v1',
    ),

    'api_key' => env('GIPHY_API_KEY'),

    'timeout' => (int) env(
        'GIPHY_TIMEOUT',
        5,
    ),

    'connect_timeout' => (int) env(
        'GIPHY_CONNECT_TIMEOUT',
        2,
    ),
];
