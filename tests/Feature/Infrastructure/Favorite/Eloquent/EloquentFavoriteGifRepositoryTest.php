<?php

declare(strict_types=1);

namespace Tests\Feature\Infrastructure\Favorite\Eloquent;

use App\Domain\Favorite\Entities\FavoriteGif;
use App\Domain\Favorite\Exceptions\FavoriteGifAlreadyExistsException;
use App\Infrastructure\Favorite\Eloquent\EloquentFavoriteGifRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EloquentFavoriteGifRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_persists_a_favorite_gif(): void
    {
        $user = User::factory()->create();

        $repository = new EloquentFavoriteGifRepository;

        $favorite = FavoriteGif::create(
            userId: (int) $user->getKey(),
            gifId: 'abc123',
            alias: 'My favorite',
        );

        $saved = $repository->save($favorite);

        self::assertNotNull($saved->id());

        self::assertSame(
            (int) $user->getKey(),
            $saved->userId(),
        );

        self::assertSame(
            'abc123',
            $saved->gifId(),
        );

        self::assertSame(
            'My favorite',
            $saved->alias(),
        );

        self::assertNotNull(
            $saved->createdAt()
        );

        $this->assertDatabaseHas(
            'favorite_gifs',
            [
                'id' => $saved->id(),
                'user_id' => $user->getKey(),
                'gif_id' => 'abc123',
                'alias' => 'My favorite',
            ],
        );

        self::assertTrue(
            $repository->existsForUser(
                userId: (int) $user->getKey(),
                gifId: 'abc123',
            )
        );
    }

    public function test_it_translates_a_unique_constraint_violation(): void
    {
        $user = User::factory()->create();

        $repository = new EloquentFavoriteGifRepository;

        $firstFavorite = FavoriteGif::create(
            userId: (int) $user->getKey(),
            gifId: 'abc123',
            alias: 'First alias',
        );

        $repository->save($firstFavorite);

        $duplicateFavorite = FavoriteGif::create(
            userId: (int) $user->getKey(),
            gifId: 'abc123',
            alias: 'Another alias',
        );

        $this->expectException(
            FavoriteGifAlreadyExistsException::class
        );

        $repository->save(
            $duplicateFavorite
        );
    }
}
