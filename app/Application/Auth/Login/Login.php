<?php

declare(strict_types=1);

namespace App\Application\Auth\Login;

use App\Domain\Auth\Exceptions\InvalidCredentialsException;
use App\Domain\Auth\Ports\AccessTokenIssuer;
use App\Domain\Auth\Ports\CredentialsAuthenticator;

final readonly class Login
{
    private const TOKEN_NAME = 'Prex Giphy API';

    public function __construct(
        private CredentialsAuthenticator $authenticator,
        private AccessTokenIssuer $tokenIssuer,
    ) {}

    public function execute(
        LoginInput $input,
    ): LoginResult {
        $user = $this->authenticator->authenticate(
            email: $input->email,
            password: $input->password,
        );

        if ($user === null) {
            throw InvalidCredentialsException::create();
        }

        $token = $this->tokenIssuer->issue(
            userId: $user->id(),
            tokenName: self::TOKEN_NAME,
        );

        return new LoginResult(
            user: $user,
            token: $token,
        );
    }
}
