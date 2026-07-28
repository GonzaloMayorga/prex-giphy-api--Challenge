<?php

declare(strict_types=1);

namespace App\Infrastructure\Favorite\Eloquent;

use App\Domain\Favorite\Entities\FavoriteGif;
use App\Domain\Favorite\Exceptions\FavoriteGifAlreadyExistsException;
use App\Domain\Favorite\Exceptions\FavoriteGifRepositoryException;
use App\Domain\Favorite\Ports\FavoriteGifRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Database\QueryException;

final class EloquentFavoriteGifRepository implements FavoriteGifRepository
{
    public function existsForUser(
        int $userId,
        string $gifId,
    ): bool {
        return FavoriteGifModel::query()
            ->where('user_id', $userId)
            ->where('gif_id', $gifId)
            ->exists();
    }

    public function save(
        FavoriteGif $favoriteGif,
    ): FavoriteGif {
        try {
            $model = FavoriteGifModel::query()->create([
                'user_id' => $favoriteGif->userId(),
                'gif_id' => $favoriteGif->gifId(),
                'alias' => $favoriteGif->alias(),
            ]);
        } catch (QueryException $exception) {
            if ($this->isUniqueViolation($exception)) {
                throw FavoriteGifAlreadyExistsException::forUserAndGif(
                    userId: $favoriteGif->userId(),
                    gifId: $favoriteGif->gifId(),
                );
            }

            throw FavoriteGifRepositoryException::saveFailed(
                $exception
            );
        }

        return $this->toDomain($model);
    }

    private function toDomain(
        FavoriteGifModel $model,
    ): FavoriteGif {
        $createdAt = $model->getAttribute(
            'created_at'
        );

        $updatedAt = $model->getAttribute(
            'updated_at'
        );

        if (! $createdAt instanceof DateTimeInterface) {
            throw FavoriteGifRepositoryException::invalidStoredData(
                'created_at is not a valid date.'
            );
        }

        if (! $updatedAt instanceof DateTimeInterface) {
            throw FavoriteGifRepositoryException::invalidStoredData(
                'updated_at is not a valid date.'
            );
        }

        return FavoriteGif::reconstitute(
            id: (int) $model->getKey(),
            userId: (int) $model->getAttribute(
                'user_id'
            ),
            gifId: (string) $model->getAttribute(
                'gif_id'
            ),
            alias: (string) $model->getAttribute(
                'alias'
            ),
            createdAt: DateTimeImmutable::createFromInterface(
                $createdAt
            ),
            updatedAt: DateTimeImmutable::createFromInterface(
                $updatedAt
            ),
        );
    }

    private function isUniqueViolation(
        QueryException $exception,
    ): bool {
        $sqlState = (string) (
            $exception->errorInfo[0]
            ?? $exception->getCode()
        );

        $driverCode = (int) (
            $exception->errorInfo[1]
            ?? 0
        );

        return $sqlState === '23505'
            || (
                $sqlState === '23000'
                && in_array(
                    $driverCode,
                    [
                        19,
                        1062,
                    ],
                    true,
                )
            );
    }
}
