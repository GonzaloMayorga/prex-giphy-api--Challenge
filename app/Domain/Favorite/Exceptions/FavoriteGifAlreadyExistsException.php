<?php

declare(strict_types=1);

namespace App\Domain\Favorite\Exceptions;

use RuntimeException;

final class FavoriteGifAlreadyExistsException extends RuntimeException
{
    public static function forUserAndGif(
        int $userId,
        string $gifId,
    ): self {
        return new self(
            sprintf(
                'The user with ID %d already saved the GIF "%s" as a favorite.',
                $userId,
                $gifId,
            )
        );
    }
}
