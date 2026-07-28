<?php

declare(strict_types=1);

namespace App\Domain\Auth\ValueObjects;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class IssuedAccessToken
{
    public function __construct(
        private string $accessToken,
        private string $tokenType,
        private int $expiresIn,
        private DateTimeImmutable $expiresAt,
    ) {
        if (trim($this->accessToken) === '') {
            throw new InvalidArgumentException(
                'The access token cannot be empty.'
            );
        }

        if (trim($this->tokenType) === '') {
            throw new InvalidArgumentException(
                'The access token type cannot be empty.'
            );
        }

        if ($this->expiresIn < 1) {
            throw new InvalidArgumentException(
                'The access token expiration must be greater than zero.'
            );
        }
    }

    public function accessToken(): string
    {
        return $this->accessToken;
    }

    public function tokenType(): string
    {
        return $this->tokenType;
    }

    public function expiresIn(): int
    {
        return $this->expiresIn;
    }

    public function expiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }
}
