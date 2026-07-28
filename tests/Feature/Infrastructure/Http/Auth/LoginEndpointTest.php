<?php

declare(strict_types=1);

namespace Tests\Feature\Infrastructure\Http\Auth;

use App\Domain\Auth\Entities\AuthenticatedUser;
use App\Domain\Auth\Ports\AccessTokenIssuer;
use App\Domain\Auth\Ports\CredentialsAuthenticator;
use App\Domain\Auth\ValueObjects\IssuedAccessToken;
use DateTimeImmutable;
use Tests\Fakes\Auth\FakeAccessTokenIssuer;
use Tests\Fakes\Auth\FakeCredentialsAuthenticator;
use Tests\TestCase;

final class LoginEndpointTest extends TestCase
{
    public function test_it_returns_an_access_token(): void
    {
        $authenticator = new FakeCredentialsAuthenticator;
        $authenticator->willAuthenticateAs(
            new AuthenticatedUser(
                id: 10,
                name: 'Challenge User',
                email: 'challenge@example.com',
            )
        );

        $tokenIssuer = new FakeAccessTokenIssuer;
        $tokenIssuer->willIssue(
            new IssuedAccessToken(
                accessToken: 'test-access-token',
                tokenType: 'Bearer',
                expiresIn: 1800,
                expiresAt: new DateTimeImmutable(
                    '2026-07-28T00:00:00+00:00'
                ),
            )
        );

        $this->bindAuthentication(
            $authenticator,
            $tokenIssuer,
        );

        $response = $this->postJson(
            '/api/login',
            [
                'email' => '  CHALLENGE@EXAMPLE.COM  ',
                'password' => 'secret-password',
            ],
        );

        $response
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    'access_token' => 'test-access-token',
                    'token_type' => 'Bearer',
                    'expires_in' => 1800,
                    'expires_at' => '2026-07-28T00:00:00+00:00',
                    'user' => [
                        'id' => 10,
                        'name' => 'Challenge User',
                        'email' => 'challenge@example.com',
                    ],
                ],
            ]);

        self::assertSame(
            'challenge@example.com',
            $authenticator->receivedEmail,
        );
    }

    public function test_it_returns_unauthorized_for_invalid_credentials(): void
    {
        $authenticator = new FakeCredentialsAuthenticator;
        $authenticator->willAuthenticateAs(null);

        $tokenIssuer = new FakeAccessTokenIssuer;

        $this->bindAuthentication(
            $authenticator,
            $tokenIssuer,
        );

        $response = $this->postJson(
            '/api/login',
            [
                'email' => 'challenge@example.com',
                'password' => 'wrong-password',
            ],
        );

        $response
            ->assertUnauthorized()
            ->assertExactJson([
                'message' => 'The provided credentials are invalid.',
                'error' => [
                    'code' => 'INVALID_CREDENTIALS',
                ],
            ]);

        self::assertNull(
            $tokenIssuer->receivedUserId
        );
    }

    public function test_it_validates_the_login_payload(): void
    {
        $authenticator = new FakeCredentialsAuthenticator;
        $tokenIssuer = new FakeAccessTokenIssuer;

        $this->bindAuthentication(
            $authenticator,
            $tokenIssuer,
        );

        $response = $this->postJson(
            '/api/login',
            [
                'email' => 'invalid-email',
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
                        'email',
                        'password',
                    ],
                ],
            ]);

        self::assertNull(
            $authenticator->receivedEmail
        );
    }

    private function bindAuthentication(
        FakeCredentialsAuthenticator $authenticator,
        FakeAccessTokenIssuer $tokenIssuer,
    ): void {
        $this->app->instance(
            CredentialsAuthenticator::class,
            $authenticator,
        );

        $this->app->instance(
            AccessTokenIssuer::class,
            $tokenIssuer,
        );
    }
}
