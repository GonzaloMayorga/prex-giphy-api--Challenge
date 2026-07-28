<?php

declare(strict_types=1);

namespace App\Domain\Favorite\Exceptions;

use RuntimeException;
use Throwable;

final class FavoriteGifRepositoryException extends RuntimeException
{
    public static function saveFailed(
        Throwable $previous,
    ): self {
        return new self(
            message: 'The favorite GIF could not be persisted.',
            previous: $previous,
        );
    }

    public static function invalidStoredData(
        string $reason,
    ): self {
        return new self(
            sprintf(
                'The favorite repository returned invalid data: %s',
                $reason,
            )
        );
    }
}
