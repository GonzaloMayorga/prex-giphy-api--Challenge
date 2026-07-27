<?php

declare(strict_types=1);

namespace Tests\Fakes\Favorite;

use App\Domain\Favorite\Entities\FavoriteGif;
use App\Domain\Favorite\Ports\FavoriteGifRepository;
use LogicException;

final class FakeFavoriteGifRepository implements
    FavoriteGifRepository
{
    private bool $exists = false;

    private ?FavoriteGif $saveResult = null;

    public ?int $receivedExistsUserId = null;
    public ?string $receivedExistsGifId = null;
    public ?FavoriteGif $receivedFavorite = null;

    public function willReportExisting(
        bool $exists,
    ): void {
        $this->exists = $exists;
    }

    public function willReturnSaved(
        FavoriteGif $favoriteGif,
    ): void {
        $this->saveResult = $favoriteGif;
    }

    public function existsForUser(
        int $userId,
        string $gifId,
    ): bool {
        $this->receivedExistsUserId = $userId;
        $this->receivedExistsGifId = $gifId;

        return $this->exists;
    }

    public function save(
        FavoriteGif $favoriteGif,
    ): FavoriteGif {
        $this->receivedFavorite = $favoriteGif;

        if ($this->saveResult === null) {
            throw new LogicException(
                'No save result was configured in FakeFavoriteGifRepository.'
            );
        }

        return $this->saveResult;
    }
}

?>
