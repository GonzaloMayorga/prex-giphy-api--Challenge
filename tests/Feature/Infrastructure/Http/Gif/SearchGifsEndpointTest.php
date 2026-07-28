<?php

declare(strict_types=1);

namespace Tests\Feature\Infrastructure\Http\Gif;

use App\Domain\Gif\Entities\Gif;
use App\Domain\Gif\Exceptions\GifProviderException;
use App\Domain\Gif\Ports\GifProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\Fakes\Gif\FakeGifProvider;
use Tests\TestCase;

final class SearchGifsEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Passport::actingAs(User::factory()->create());
    }

    public function test_it_returns_gif_search_results(): void
    {
        $gif = new Gif(
            id: 'abc123',
            title: 'Funny cat',
            originalUrl: 'https://example.com/original.gif',
            previewUrl: 'https://example.com/preview.gif',
            username: 'cat-user',
        );

        $provider = new FakeGifProvider;
        $provider->willReturnSearchResults([$gif]);

        $this->bindProvider($provider);

        $query = http_build_query([
            'query' => '  cats  ',
            'limit' => 20,
            'offset' => 5,
        ]);

        $response = $this->getJson(
            '/api/gifs?'.$query
        );

        $response
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    [
                        'id' => 'abc123',
                        'title' => 'Funny cat',
                        'username' => 'cat-user',
                        'original_url' => 'https://example.com/original.gif',
                        'preview_url' => 'https://example.com/preview.gif',
                    ],
                ],
                'meta' => [
                    'query' => 'cats',
                    'limit' => 20,
                    'offset' => 5,
                    'count' => 1,
                ],
            ]);

        self::assertSame(
            'cats',
            $provider->receivedQuery,
        );

        self::assertSame(
            20,
            $provider->receivedLimit,
        );

        self::assertSame(
            5,
            $provider->receivedOffset,
        );
    }

    public function test_it_uses_default_pagination_values(): void
    {
        $provider = new FakeGifProvider;
        $provider->willReturnSearchResults([]);

        $this->bindProvider($provider);

        $response = $this->getJson(
            '/api/gifs?query=dogs'
        );

        $response
            ->assertOk()
            ->assertExactJson([
                'data' => [],
                'meta' => [
                    'query' => 'dogs',
                    'limit' => 10,
                    'offset' => 0,
                    'count' => 0,
                ],
            ]);

        self::assertSame(
            10,
            $provider->receivedLimit,
        );

        self::assertSame(
            0,
            $provider->receivedOffset,
        );
    }

    public function test_it_rejects_a_missing_query(): void
    {
        $provider = new FakeGifProvider;

        $this->bindProvider($provider);

        $response = $this->getJson(
            '/api/gifs'
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'error.code',
                'VALIDATION_ERROR',
            )
            ->assertJsonStructure([
                'message',
                'error' => [
                    'code',
                    'details' => [
                        'query',
                    ],
                ],
            ]);

        self::assertNull(
            $provider->receivedQuery
        );
    }

    public function test_it_rejects_an_invalid_limit(): void
    {
        $provider = new FakeGifProvider;

        $this->bindProvider($provider);

        $response = $this->getJson(
            '/api/gifs?query=cats&limit=51'
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
                        'limit',
                    ],
                ],
            ]);
    }

    public function test_it_rejects_an_invalid_offset(): void
    {
        $provider = new FakeGifProvider;

        $this->bindProvider($provider);

        $response = $this->getJson(
            '/api/gifs?query=cats&offset=5000'
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
                        'offset',
                    ],
                ],
            ]);
    }

    public function test_it_returns_bad_gateway_when_the_provider_fails(): void
    {
        $provider = new FakeGifProvider;

        $provider->willFailSearchWith(
            GifProviderException::requestFailed(
                statusCode: 500,
                providerMessage: 'Internal error',
            )
        );

        $this->bindProvider($provider);

        $response = $this->getJson(
            '/api/gifs?query=cats'
        );

        $response
            ->assertStatus(502)
            ->assertExactJson([
                'message' => 'The GIF provider is temporarily unavailable.',
                'error' => [
                    'code' => 'GIF_PROVIDER_UNAVAILABLE',
                ],
            ]);
    }

    private function bindProvider(
        FakeGifProvider $provider,
    ): void {
        $this->app->instance(
            GifProvider::class,
            $provider,
        );
    }
}
