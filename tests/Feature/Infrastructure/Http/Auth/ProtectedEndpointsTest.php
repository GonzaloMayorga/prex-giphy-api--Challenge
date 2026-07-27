<?php

declare(strict_types=1);

namespace Tests\Feature\Infrastructure\Http\Auth;

use App\Domain\Gif\Ports\GifProvider;
use Tests\Fakes\Gif\FakeGifProvider;
use Tests\TestCase;

final class ProtectedEndpointsTest extends TestCase
{
    public function test_search_requires_authentication(): void
    {
        $provider = new FakeGifProvider();

        $this->app->instance(
            GifProvider::class,
            $provider,
        );

        $response = $this->getJson(
            '/api/gifs?query=cats'
        );

        $response
            ->assertUnauthorized()
            ->assertExactJson([
                'message' => 'Authentication is required to access this resource.',
                'error' => [
                    'code' => 'UNAUTHENTICATED',
                ],
            ]);

        self::assertNull(
            $provider->receivedQuery
        );
    }

    public function test_find_by_id_requires_authentication(): void
    {
        $provider = new FakeGifProvider();

        $this->app->instance(
            GifProvider::class,
            $provider,
        );

        $response = $this->getJson(
            '/api/gifs/abc123'
        );

        $response
            ->assertUnauthorized()
            ->assertJsonPath(
                'error.code',
                'UNAUTHENTICATED',
            );

        self::assertNull(
            $provider->receivedId
        );
    }
}

?>
