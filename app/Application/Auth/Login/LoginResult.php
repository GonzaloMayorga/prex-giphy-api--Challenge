<?php

declare(strict_types=1);

namespace App\Application\Auth\Login;

use App\Domain\Auth\Entities\AuthenticatedUser;
use App\Domain\Auth\ValueObjects\IssuedAccessToken;

final readonly class LoginResult
{
    public function __construct(
        private AuthenticatedUser $user,
        private IssuedAccessToken $token,
    ) {}

    public function user(): AuthenticatedUser
    {
        return $this->user;
    }

    public function token(): IssuedAccessToken
    {
        return $this->token;
    }
}
