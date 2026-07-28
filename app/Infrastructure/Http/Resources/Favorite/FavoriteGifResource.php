<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Resources\Favorite;

use App\Domain\Favorite\Entities\FavoriteGif;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use LogicException;

final class FavoriteGifResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (! $this->resource instanceof FavoriteGif) {
            throw new LogicException(
                'FavoriteGifResource expects a FavoriteGif instance.'
            );
        }

        $id = $this->resource->id();
        $createdAt = $this->resource->createdAt();
        $updatedAt = $this->resource->updatedAt();

        if (
            $id === null
            || $createdAt === null
            || $updatedAt === null
        ) {
            throw new LogicException(
                'FavoriteGifResource expects a persisted favorite.'
            );
        }

        return [
            'id' => $id,
            'user_id' => $this->resource->userId(),
            'gif_id' => $this->resource->gifId(),
            'alias' => $this->resource->alias(),
            'created_at' => $createdAt->format(
                DATE_ATOM
            ),
            'updated_at' => $updatedAt->format(
                DATE_ATOM
            ),
        ];
    }
}
