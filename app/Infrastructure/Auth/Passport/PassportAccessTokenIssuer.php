<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth\Passport;

use App\Domain\Auth\Ports\AccessTokenIssuer;
use App\Domain\Auth\ValueObjects\IssuedAccessToken;
use App\Models\User;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;

final readonly class PassportAccessTokenIssuer implements AccessTokenIssuer
{
    public function __construct(
        private int $ttlSeconds,
    ) {
        if ($this->ttlSeconds < 1) {
            throw new RuntimeException(
                'The access token TTL must be greater than zero.'
            );
        }
    }

    public function issue(
        int $userId,
        string $tokenName,
    ): IssuedAccessToken {
        $user = User::query()->find($userId);

        if ($user === null) {
            throw new RuntimeException(
                sprintf(
                    'The user with ID %d could not be found while issuing an access token.',
                    $userId,
                )
            );
        }

        $tokenResult = $user->createToken(
            $tokenName
        );

        $issuedAt = new DateTimeImmutable(
            'now',
            new DateTimeZone('UTC'),
        );

        $expiresAt = $issuedAt->modify(
            sprintf(
                '+%d seconds',
                $this->ttlSeconds,
            )
        );

        return new IssuedAccessToken(
            accessToken: $tokenResult->accessToken,
            tokenType: 'Bearer',
            expiresIn: $this->ttlSeconds,
            expiresAt: $expiresAt,
        );
    }
}
