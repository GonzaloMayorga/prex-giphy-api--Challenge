<?php

declare(strict_types=1);

namespace App\Application\Favorite\Save;

use App\Domain\Favorite\Entities\FavoriteGif;
use InvalidArgumentException;

final readonly class SaveFavoriteGifInput
{
    public int $authenticatedUserId;

    public int $requestedUserId;

    public string $gifId;

    public string $alias;

    public function __construct(
        int $authenticatedUserId,
        int $requestedUserId,
        string $gifId,
        string $alias,
    ) {
        if ($authenticatedUserId < 1) {
            throw new InvalidArgumentException(
                'The authenticated user ID must be greater than zero.'
            );
        }

        if ($requestedUserId < 1) {
            throw new InvalidArgumentException(
                'The requested user ID must be greater than zero.'
            );
        }

        $normalizedGifId = trim($gifId);

        $normalizedAlias = preg_replace(
            '/\s+/u',
            ' ',
            trim($alias),
        );

        if (! is_string($normalizedAlias)) {
            $normalizedAlias = trim($alias);
        }

        if ($normalizedGifId === '') {
            throw new InvalidArgumentException(
                'The favorite GIF ID cannot be empty.'
            );
        }

        if (
            mb_strlen($normalizedGifId)
            > FavoriteGif::MAX_GIF_ID_LENGTH
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'The favorite GIF ID cannot exceed %d characters.',
                    FavoriteGif::MAX_GIF_ID_LENGTH,
                )
            );
        }

        if ($normalizedAlias === '') {
            throw new InvalidArgumentException(
                'The favorite alias cannot be empty.'
            );
        }

        if (
            mb_strlen($normalizedAlias)
            > FavoriteGif::MAX_ALIAS_LENGTH
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'The favorite alias cannot exceed %d characters.',
                    FavoriteGif::MAX_ALIAS_LENGTH,
                )
            );
        }

        $this->authenticatedUserId = $authenticatedUserId;
        $this->requestedUserId = $requestedUserId;
        $this->gifId = $normalizedGifId;
        $this->alias = $normalizedAlias;
    }
}
