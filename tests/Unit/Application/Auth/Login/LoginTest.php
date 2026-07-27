<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Auth\Login;

use App\Application\Auth\Login\Login;
use App\Application\Auth\Login\LoginInput;
use App\Domain\Auth\Entities\AuthenticatedUser;
use App\Domain\Auth\Exceptions\InvalidCredentialsException;
use App\Domain\Auth\ValueObjects\IssuedAccessToken;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\Auth\FakeAccessTokenIssuer;
use Tests\Fakes\Auth\FakeCredentialsAuthenticator;

final class LoginTest extends TestCase
{
    public function test_it_authenticates_and_issues_a_token(): void
    {
        $user = new AuthenticatedUser(
            id: 10,
            name: 'Challenge User',
            email: 'challenge@example.com',
        );

        $token = new IssuedAccessToken(
            accessToken: 'test-access-token',
            tokenType: 'Bearer',
            expiresIn: 1800,
            expiresAt: new DateTimeImmutable(
                '2026-07-28T00:00:00+00:00'
            ),
        );

        $authenticator = new FakeCredentialsAuthenticator();
        $authenticator->willAuthenticateAs($user);

        $tokenIssuer = new FakeAccessTokenIssuer();
        $tokenIssuer->willIssue($token);

        $useCase = new Login(
            authenticator: $authenticator,
            tokenIssuer: $tokenIssuer,
        );

        $result = $useCase->execute(
            new LoginInput(
                email: '  CHALLENGE@EXAMPLE.COM  ',
                password: 'secret-password',
            )
        );

        self::assertSame(
            $user,
            $result->user(),
        );

        self::assertSame(
            $token,
            $result->token(),
        );

        self::assertSame(
            'challenge@example.com',
            $authenticator->receivedEmail,
        );

        self::assertSame(
            'secret-password',
            $authenticator->receivedPassword,
        );

        self::assertSame(
            10,
            $tokenIssuer->receivedUserId,
        );

        self::assertSame(
            'Prex Giphy API',
            $tokenIssuer->receivedTokenName,
        );
    }

    public function test_it_rejects_invalid_credentials(): void
    {
        $authenticator = new FakeCredentialsAuthenticator();
        $authenticator->willAuthenticateAs(null);

        $tokenIssuer = new FakeAccessTokenIssuer();

        $useCase = new Login(
            authenticator: $authenticator,
            tokenIssuer: $tokenIssuer,
        );

        $this->expectException(
            InvalidCredentialsException::class
        );

        $useCase->execute(
            new LoginInput(
                email: 'challenge@example.com',
                password: 'wrong-password',
            )
        );
    }

    public function test_it_rejects_an_invalid_email(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'The login email must be valid.'
        );

        new LoginInput(
            email: 'not-an-email',
            password: 'secret-password',
        );
    }

    public function test_it_rejects_an_empty_password(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'The login password cannot be empty.'
        );

        new LoginInput(
            email: 'challenge@example.com',
            password: '',
        );
    }
}

?>
