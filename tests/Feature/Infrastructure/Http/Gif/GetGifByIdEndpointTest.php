<?php

declare(strict_types=1);

namespace Tests\Feature\Infrastructure\Http\Gif;

use App\Domain\Gif\Entities\Gif;
use App\Domain\Gif\Exceptions\GifProviderException;
use App\Domain\Gif\Ports\GifProvider;
use Tests\Fakes\Gif\FakeGifProvider;
use Tests\TestCase;

final class GetGifByIdEndpointTest extends TestCase
{
    public function test_it_returns_a_gif_by_id(): void
    {
        $gif = new Gif(
            id: 'abc123',
            title: 'Funny cat',
            originalUrl: 'https://example.com/original.gif',
            previewUrl: 'https://example.com/preview.gif',
            username: 'cat-user',
        );

        $provider = new FakeGifProvider();
        $provider->willReturnGifById($gif);

        $this->bindProvider($provider);

        $response = $this->getJson(
            '/api/gifs/abc123'
        );

        $response
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    'id' => 'abc123',
                    'title' => 'Funny cat',
                    'username' => 'cat-user',
                    'original_url' => 'https://example.com/original.gif',
                    'preview_url' => 'https://example.com/preview.gif',
                ],
            ]);

        self::assertSame(
            'abc123',
            $provider->receivedId,
        );
    }

    public function test_it_returns_not_found_when_the_gif_does_not_exist(): void
    {
        $provider = new FakeGifProvider();
        $provider->willReturnGifById(null);

        $this->bindProvider($provider);

        $response = $this->getJson(
            '/api/gifs/missing'
        );

        $response
            ->assertNotFound()
            ->assertExactJson([
                'message' => 'The Gif with ID "missing" was not found.',
                'error' => [
                    'code' => 'GIF_NOT_FOUND',
                ],
            ]);

        self::assertSame(
            'missing',
            $provider->receivedId,
        );
    }

    public function test_it_rejects_an_excessively_long_id(): void
    {
        $provider = new FakeGifProvider();

        $this->bindProvider($provider);

        $id = str_repeat('a', 101);

        $response = $this->getJson(
            '/api/gifs/'.$id
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
                        'id',
                    ],
                ],
            ]);

        self::assertNull(
            $provider->receivedId
        );
    }

    public function test_it_returns_bad_gateway_when_the_provider_fails(): void
    {
        $provider = new FakeGifProvider();

        $provider->willFailGetByIdWith(
            GifProviderException::requestFailed(
                statusCode: 503,
                providerMessage: 'Service unavailable',
            )
        );

        $this->bindProvider($provider);

        $response = $this->getJson(
            '/api/gifs/abc123'
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