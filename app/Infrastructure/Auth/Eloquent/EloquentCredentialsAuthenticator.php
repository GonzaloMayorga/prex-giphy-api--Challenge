<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth\Eloquent;

use App\Domain\Auth\Entities\AuthenticatedUser;
use App\Domain\Auth\Ports\CredentialsAuthenticator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class EloquentCredentialsAuthenticator implements
    CredentialsAuthenticator
{
    public function authenticate(
        string $email,
        string $password,
    ): ?AuthenticatedUser {
        $user = User::query()
            ->where('email', $email)
            ->first();

        if ($user === null) {
            return null;
        }

        if (
            !Hash::check(
                $password,
                (string) $user->getAuthPassword(),
            )
        ) {
            return null;
        }

        if (
            Hash::needsRehash(
                (string) $user->getAuthPassword()
            )
        ) {
            $user->forceFill([
                'password' => Hash::make($password),
            ])->save();
        }

        return new AuthenticatedUser(
            id: (int) $user->getKey(),
            name: (string) $user->name,
            email: (string) $user->email,
        );
    }
}

?>