<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Domain\Gif\Ports\GifProvider;
use App\Infrastructure\Gif\Giphy\GiphyApiAdapter;
use App\Infrastructure\Gif\Giphy\GiphyGifMapper;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

final class HexagonalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            GifProvider::class,
            function (Application $app): GifProvider {
                $apiKey = (string) config(
                    'giphy.api_key',
                    ''
                );

                if (trim($apiKey) === '') {
                    throw new InvalidArgumentException(
                        'GIPHY_API_KEY is not configured.'
                    );
                }

                return new GiphyApiAdapter(
                    http: $app->make(Factory::class),
                    mapper: $app->make(
                        GiphyGifMapper::class
                    ),
                    baseUrl: (string) config(
                        'giphy.base_url'
                    ),
                    apiKey: $apiKey,
                    timeoutSeconds: (int) config(
                        'giphy.timeout'
                    ),
                    connectTimeoutSeconds: (int) config(
                        'giphy.connect_timeout'
                    ),
                );
            },
        );
    }

    public function boot(): void
    {
    }
}
