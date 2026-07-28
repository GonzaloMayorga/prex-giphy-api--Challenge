<?php

declare(strict_types=1);

namespace App\Domain\Audit\Exceptions;

use RuntimeException;
use Throwable;

final class ApiInteractionRepositoryException extends RuntimeException
{
    public static function persistenceFailed(
        Throwable $previous,
    ): self {
        return new self(
            message: 'The API interaction audit could not be persisted.',
            previous: $previous,
        );
    }
}
