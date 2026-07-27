<?php

declare(strict_types=1);

namespace Tests\Fakes\Auth;

use App\Domain\Auth\Entities\AuthenticatedUser;
use App\Domain\Auth\Ports\CredentialsAuthenticator;

final class FakeCredentialsAuthenticator implements
    CredentialsAuthenticator
{
    private ?AuthenticatedUser $user = null;

    public ?string $receivedEmail = null;
    public ?string $receivedPassword = null;

    public function willAuthenticateAs(
        ?AuthenticatedUser $user,
    ): void {
        $this->user = $user;
    }

    public function authenticate(
        string $email,
        string $password,
    ): ?AuthenticatedUser {
        $this->receivedEmail = $email;
        $this->receivedPassword = $password;

        return $this->user;
    }
}

?>
