<?php

declare(strict_types=1);

return [
    'access_token_ttl_minutes' => (int) env(
        'PASSPORT_ACCESS_TOKEN_TTL_MINUTES',
        30
    ),
];

?>