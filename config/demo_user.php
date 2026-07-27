<?php

declare(strict_types=1);

return [
    'name' => env(
        'DEMO_USER_NAME',
        'Challenge User',
    ),

    'email' => env(
        'DEMO_USER_EMAIL',
        'challenge@example.com',
    ),

    'password' => env(
        'DEMO_USER_PASSWORD',
    ),
];

?>
