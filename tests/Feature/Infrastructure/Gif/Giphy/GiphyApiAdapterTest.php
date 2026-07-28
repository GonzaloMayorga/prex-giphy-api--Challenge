<?php

declare(strict_types=1);

namespace Tests\Feature\Infrastructure\Gif\Giphy;

use App\Domain\Gif\Exceptions\GifProviderException;
use App\Infrastructure\Gif\Giphy\GiphyApiAdapter;
use App\Infrastructure\Gif\Giphy\GiphyGifMapper;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class GiphyApiAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    public function test_it_searches_gifs_using_giphy(): void
    {
        Http::fake([
            'https://api.giphy.com/v1/gifs/search*' => Http::response([
                'data' => [
                    $this->giphyGifPayload(),
                ],
                'pagination' => [
                    'offset' => 5,
                    'count' => 1,
                    'total_count' => 100,
                ],
                'meta' => [
                    'status' => 200,
                    'msg' => 'OK',
                    'response_id' => 'response-123',
                ],
            ], 200),
        ]);

        $result = $this->adapter()->search(
            query: 'cats',
            limit: 20,
            offset: 5,
        );

        self::assertCount(1, $result);

        $gif = $result->items()[0];

        self::assertSame('abc123', $gif->id());
        self::assertSame('Funny cat', $gif->title());

        Http::assertSent(
            function (Request $request): bool {
                $query = [];

                parse_str(
                    (string) parse_url(
                        $request->url(),
                        PHP_URL_QUERY,
                    ),
                    $query,
                );

                return $request->method() === 'GET'
                    && str_starts_with(
                        $request->url(),
                        'https://api.giphy.com/v1/gifs/search'
                    )
                    && ($query['api_key'] ?? null)
                        === 'test-api-key'
                    && ($query['q'] ?? null) === 'cats'
                    && ($query['limit'] ?? null) === '20'
                    && ($query['offset'] ?? null) === '5';
            }
        );
    }

    public function test_it_gets_a_gif_by_id(): void
    {
        Http::fake([
            'https://api.giphy.com/v1/gifs/abc123*' => Http::response([
                'data' => $this->giphyGifPayload(),
                'meta' => [
                    'status' => 200,
                    'msg' => 'OK',
                    'response_id' => 'response-123',
                ],
            ], 200),
        ]);

        $gif = $this->adapter()->getById('abc123');

        self::assertNotNull($gif);
        self::assertSame('abc123', $gif->id());
        self::assertSame('Funny cat', $gif->title());
    }

    public function test_it_returns_null_when_the_gif_is_not_found(): void
    {
        Http::fake([
            'https://api.giphy.com/v1/gifs/missing*' => Http::response([
                'data' => [],
                'meta' => [
                    'status' => 404,
                    'msg' => 'Not Found',
                    'response_id' => 'response-404',
                ],
            ], 404),
        ]);

        $gif = $this->adapter()->getById('missing');

        self::assertNull($gif);
    }

    public function test_it_translates_an_unsuccessful_response(): void
    {
        Http::fake([
            'https://api.giphy.com/v1/gifs/search*' => Http::response([
                'data' => [],
                'meta' => [
                    'status' => 500,
                    'msg' => 'Internal Server Error',
                    'response_id' => 'response-500',
                ],
            ], 500),
        ]);

        $this->expectException(
            GifProviderException::class
        );

        $this->expectExceptionMessage(
            'The Gif provider returned an error with status code 500.'
        );

        $this->adapter()->search(
            query: 'cats',
            limit: 10,
            offset: 0,
        );
    }

    public function test_it_rejects_a_malformed_response(): void
    {
        Http::fake([
            'https://api.giphy.com/v1/gifs/search*' => Http::response([
                'unexpected' => 'value',
                'meta' => [
                    'status' => 200,
                    'msg' => 'OK',
                    'response_id' => 'response-123',
                ],
            ], 200),
        ]);

        $this->expectException(
            GifProviderException::class
        );

        $this->expectExceptionMessage(
            'The "data" field must be an array.'
        );

        $this->adapter()->search(
            query: 'cats',
            limit: 10,
            offset: 0,
        );
    }

    public function test_it_rejects_a_synthetic_response(): void
    {
        Http::fake([
            'https://api.giphy.com/v1/gifs/search*' => Http::response([
                'data' => [],
                'meta' => [
                    'status' => 200,
                    'msg' => 'OK',
                    'response_id' => '',
                ],
            ], 200),
        ]);

        $this->expectException(
            GifProviderException::class
        );

        $this->expectExceptionMessage(
            'GIPHY returned a synthetic error response.'
        );

        $this->adapter()->search(
            query: 'cats',
            limit: 10,
            offset: 0,
        );
    }

    private function adapter(): GiphyApiAdapter
    {
        return new GiphyApiAdapter(
            http: $this->app->make(Factory::class),
            mapper: new GiphyGifMapper,
            baseUrl: 'https://api.giphy.com/v1',
            apiKey: 'test-api-key',
            timeoutSeconds: 5,
            connectTimeoutSeconds: 2,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function giphyGifPayload(): array
    {
        return [
            'id' => 'abc123',
            'title' => 'Funny cat',
            'username' => 'cat-user',
            'images' => [
                'original' => [
                    'url' => 'https://example.com/original.gif',
                ],
                'preview_gif' => [
                    'url' => 'https://example.com/preview.gif',
                ],
            ],
        ];
    }
}
