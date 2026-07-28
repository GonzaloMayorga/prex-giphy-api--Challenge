<?php

declare(strict_types=1);

namespace App\Domain\Auth\Ports;

use App\Domain\Auth\ValueObjects\IssuedAccessToken;

interface AccessTokenIssuer
{
    public function issue(
        int $userId,
        string $tokenName,
    ): IssuedAccessToken;
}
