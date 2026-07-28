<?php

declare(strict_types=1);

namespace App\Domain\Auth\Ports;

use App\Domain\Auth\Entities\AuthenticatedUser;

interface CredentialsAuthenticator
{
    public function authenticate(
        string $email,
        string $password,
    ): ?AuthenticatedUser;
}
