<?php

declare(strict_types=1);

namespace App\Domain\Favorite\Exceptions;

use RuntimeException;

final class FavoriteGifOwnershipException extends RuntimeException
{
    public static function userMismatch(
        int $authenticatedUserId,
        int $requestedUserId,
    ): self {
        return new self(
            sprintf(
                'The authenticated user %d cannot save favorites for user %d.',
                $authenticatedUserId,
                $requestedUserId,
            )
        );
    }
}
