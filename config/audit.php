<?php

declare(strict_types=1);

return [
    'enabled' => (bool) env(
        'AUDIT_ENABLED',
        true,
    ),

    'max_payload_bytes' => (int) env(
        'AUDIT_MAX_PAYLOAD_BYTES',
        1048576,
    ),

    'redacted_value' => '[REDACTED]',

    'sensitive_keys' => [
        'password',
        'password_confirmation',
        'access_token',
        'refresh_token',
        'client_secret',
        'authorization',
        'token',
    ],
];
