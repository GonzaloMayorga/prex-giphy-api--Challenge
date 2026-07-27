<?php

declare(strict_types=1);

namespace Tests\Feature\Infrastructure\Http\Favorite;

use App\Domain\Favorite\Entities\FavoriteGif;
use App\Domain\Favorite\Ports\FavoriteGifRepository;
use App\Domain\Gif\Entities\Gif;
use App\Domain\Gif\Ports\GifProvider;
use App\Models\User;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\Fakes\Favorite\FakeFavoriteGifRepository;
use Tests\Fakes\Gif\FakeGifProvider;
use Tests\TestCase;

final class SaveFavoriteGifEndpointTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        Passport::actingAs($this->user);
    }

    public function test_it_saves_a_favorite_gif(): void
    {
        $provider = new FakeGifProvider();

        $provider->willReturnGifById(
            new Gif(
                id: 'abc123',
                title: 'Funny cat',
                originalUrl: 'https://example.com/original.gif',
            )
        );

        $repository = new FakeFavoriteGifRepository();
        $repository->willReportExisting(false);

        $repository->willReturnSaved(
            FavoriteGif::reconstitute(
                id: 50,
                userId: (int) $this->user->getKey(),
                gifId: 'abc123',
                alias: 'My favorite cat',
                createdAt: new DateTimeImmutable(
                    '2026-07-28T01:00:00+00:00'
                ),
                updatedAt: new DateTimeImmutable(
                    '2026-07-28T01:00:00+00:00'
                ),
            )
        );

        $this->bindDependencies(
            $provider,
            $repository,
        );

        $response = $this->postJson(
            '/api/favorites',
            [
                'gif_id' => '  abc123  ',
                'alias' => '  My   favorite   cat  ',
                'user_id' => $this->user->getKey(),
            ],
        );

        $response
            ->assertCreated()
            ->assertExactJson([
                'data' => [
                    'id' => 50,
                    'user_id' => $this->user->getKey(),
                    'gif_id' => 'abc123',
                    'alias' => 'My favorite cat',
                    'created_at' => '2026-07-28T01:00:00+00:00',
                    'updated_at' => '2026-07-28T01:00:00+00:00',
                ],
            ]);

        self::assertSame(
            'abc123',
            $provider->receivedId,
        );

        self::assertNotNull(
            $repository->receivedFavorite
        );
    }

    public function test_it_rejects_another_user_id(): void
    {
        $provider = new FakeGifProvider();
        $repository = new FakeFavoriteGifRepository();

        $this->bindDependencies(
            $provider,
            $repository,
        );

        $response = $this->postJson(
            '/api/favorites',
            [
                'gif_id' => 'abc123',
                'alias' => 'My favorite',
                'user_id' => $this->user->getKey() + 1,
            ],
        );

        $response
            ->assertForbidden()
            ->assertExactJson([
                'message' => 'You cannot save favorites for another user.',
                'error' => [
                    'code' => 'FAVORITE_USER_FORBIDDEN',
                ],
            ]);

        self::assertNull(
            $provider->receivedId
        );

        self::assertNull(
            $repository->receivedExistsUserId
        );
    }

    public function test_it_returns_conflict_for_a_duplicate(): void
    {
        $provider = new FakeGifProvider();

        $repository = new FakeFavoriteGifRepository();
        $repository->willReportExisting(true);

        $this->bindDependencies(
            $provider,
            $repository,
        );

        $response = $this->postJson(
            '/api/favorites',
            [
                'gif_id' => 'abc123',
                'alias' => 'My favorite',
                'user_id' => $this->user->getKey(),
            ],
        );

        $response
            ->assertStatus(409)
            ->assertExactJson([
                'message' => 'The GIF is already saved as a favorite.',
                'error' => [
                    'code' => 'FAVORITE_GIF_ALREADY_EXISTS',
                ],
            ]);

        self::assertNull(
            $provider->receivedId
        );
    }

    public function test_it_returns_not_found_for_an_unknown_gif(): void
    {
        $provider = new FakeGifProvider();
        $provider->willReturnGifById(null);

        $repository = new FakeFavoriteGifRepository();
        $repository->willReportExisting(false);

        $this->bindDependencies(
            $provider,
            $repository,
        );

        $response = $this->postJson(
            '/api/favorites',
            [
                'gif_id' => 'missing',
                'alias' => 'Missing favorite',
                'user_id' => $this->user->getKey(),
            ],
        );

        $response
            ->assertNotFound()
            ->assertJsonPath(
                'error.code',
                'GIF_NOT_FOUND',
            );

        self::assertNull(
            $repository->receivedFavorite
        );
    }

    public function test_it_validates_the_payload(): void
    {
        $provider = new FakeGifProvider();
        $repository = new FakeFavoriteGifRepository();

        $this->bindDependencies(
            $provider,
            $repository,
        );

        $response = $this->postJson(
            '/api/favorites',
            [
                'gif_id' => '',
                'alias' => '',
                'user_id' => 0,
            ],
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'error.code',
                'VALIDATION_ERROR',
            )
            ->assertJsonStructure([
                'error' => [
                    'details' => [
                        'gif_id',
                        'alias',
                        'user_id',
                    ],
                ],
            ]);

        self::assertNull(
            $repository->receivedExistsUserId
        );
    }

    private function bindDependencies(
        FakeGifProvider $provider,
        FakeFavoriteGifRepository $repository,
    ): void {
        $this->app->instance(
            GifProvider::class,
            $provider,
        );

        $this->app->instance(
            FavoriteGifRepository::class,
            $repository,
        );
    }
}

?>
