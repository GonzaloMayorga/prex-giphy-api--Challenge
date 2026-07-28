<?php

declare(strict_types=1);

namespace App\Domain\Favorite\Ports;

use App\Domain\Favorite\Entities\FavoriteGif;

interface FavoriteGifRepository
{
    public function existsForUser(
        int $userId,
        string $gifId,
    ): bool;

    public function save(
        FavoriteGif $favoriteGif,
    ): FavoriteGif;
}
