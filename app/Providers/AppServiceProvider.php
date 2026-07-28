<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\CarbonInterval;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use LogicException;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $tokenLifetimeMinutes = (int) config(
            'auth_tokens.access_token_ttl_minutes',
            30,
        );

        if ($tokenLifetimeMinutes < 1) {
            throw new LogicException(
                'The Passport access token lifetime must be greater than zero.'
            );
        }

        Passport::personalAccessTokensExpireIn(
            CarbonInterval::minutes(
                $tokenLifetimeMinutes
            )
        );
    }
}
