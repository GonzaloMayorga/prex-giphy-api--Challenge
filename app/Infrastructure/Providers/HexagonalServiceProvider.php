<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Domain\Auth\Ports\AccessTokenIssuer;
use App\Domain\Auth\Ports\CredentialsAuthenticator;
use App\Domain\Gif\Ports\GifProvider;
use App\Infrastructure\Auth\Eloquent\EloquentCredentialsAuthenticator;
use App\Infrastructure\Auth\Passport\PassportAccessTokenIssuer;
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
        $this->registerGifProvider();
        $this->registerAuthentication();
    }

    public function boot(): void
    {
    }

    private function registerGifProvider(): void
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

    private function registerAuthentication(): void
    {
        $this->app->bind(
            CredentialsAuthenticator::class,
            EloquentCredentialsAuthenticator::class,
        );

        $this->app->bind(
            AccessTokenIssuer::class,
            function (): AccessTokenIssuer {
                $ttlMinutes = (int) config(
                    'auth_tokens.access_token_ttl_minutes',
                    30,
                );

                return new PassportAccessTokenIssuer(
                    ttlSeconds: $ttlMinutes * 60,
                );
            },
        );
    }
}