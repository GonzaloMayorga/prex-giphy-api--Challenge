<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\Favorite;

use App\Application\Favorite\Save\SaveFavoriteGif;
use App\Application\Favorite\Save\SaveFavoriteGifInput;
use App\Infrastructure\Http\Requests\Favorite\SaveFavoriteGifRequest;
use App\Infrastructure\Http\Resources\Favorite\FavoriteGifResource;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use LogicException;
use Symfony\Component\HttpFoundation\Response;

final readonly class SaveFavoriteGifController
{
    public function __construct(
        private SaveFavoriteGif $saveFavoriteGif,
    ) {
    }

    public function __invoke(
        SaveFavoriteGifRequest $request,
    ): JsonResponse {
        $validated = $request->validated();

        $authenticatedUser = $request->user();

        if (
            !$authenticatedUser
            instanceof Authenticatable
        ) {
            throw new LogicException(
                'An authenticated user is required to save a favorite.'
            );
        }

        $favorite = $this->saveFavoriteGif->execute(
            new SaveFavoriteGifInput(
                authenticatedUserId: (int) $authenticatedUser
                    ->getAuthIdentifier(),
                requestedUserId: (int) $validated['user_id'],
                gifId: (string) $validated['gif_id'],
                alias: (string) $validated['alias'],
            )
        );

        return (
            new FavoriteGifResource($favorite)
        )
            ->response()
            ->setStatusCode(
                Response::HTTP_CREATED
            );
    }
}

?>
