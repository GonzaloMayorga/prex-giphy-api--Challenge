<?php

declare(strict_types=1);

namespace App\Domain\Auth\Entities;

use InvalidArgumentException;

final readonly class AuthenticatedUser
{
    public function __construct(
        private int $id,
        private string $name,
        private string $email,
    ) {
        if ($this->id < 1) {
            throw new InvalidArgumentException(
                'The authenticated user ID must be greater than zero.'
            );
        }

        if (trim($this->name) === '') {
            throw new InvalidArgumentException(
                'The authenticated user name cannot be empty.'
            );
        }

        if (
            filter_var(
                $this->email,
                FILTER_VALIDATE_EMAIL,
            ) === false
        ) {
            throw new InvalidArgumentException(
                'The authenticated user email must be valid.'
            );
        }
    }

    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): string
    {
        return $this->email;
    }
}

?>