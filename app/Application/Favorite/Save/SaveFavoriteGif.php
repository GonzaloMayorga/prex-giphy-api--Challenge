<?php

declare(strict_types=1);

namespace App\Application\Favorite\Save;

use App\Domain\Favorite\Entities\FavoriteGif;
use App\Domain\Favorite\Exceptions\FavoriteGifAlreadyExistsException;
use App\Domain\Favorite\Exceptions\FavoriteGifOwnershipException;
use App\Domain\Favorite\Ports\FavoriteGifRepository;
use App\Domain\Gif\Exceptions\GifNotFoundException;
use App\Domain\Gif\Ports\GifProvider;

final readonly class SaveFavoriteGif
{
    public function __construct(
        private FavoriteGifRepository $favoriteRepository,
        private GifProvider $gifProvider,
    ) {
    }

    public function execute(
        SaveFavoriteGifInput $input,
    ): FavoriteGif {
        if (
            $input->authenticatedUserId
            !== $input->requestedUserId
        ) {
            throw FavoriteGifOwnershipException::userMismatch(
                authenticatedUserId: $input->authenticatedUserId,
                requestedUserId: $input->requestedUserId,
            );
        }

        if (
            $this->favoriteRepository->existsForUser(
                userId: $input->requestedUserId,
                gifId: $input->gifId,
            )
        ) {
            throw FavoriteGifAlreadyExistsException::forUserAndGif(
                userId: $input->requestedUserId,
                gifId: $input->gifId,
            );
        }

        $gif = $this->gifProvider->getById(
            $input->gifId
        );

        if ($gif === null) {
            throw GifNotFoundException::withId(
                $input->gifId
            );
        }

        $favorite = FavoriteGif::create(
            userId: $input->requestedUserId,
            gifId: $gif->id(),
            alias: $input->alias,
        );

        return $this->favoriteRepository->save(
            $favorite
        );
    }
}

?>
