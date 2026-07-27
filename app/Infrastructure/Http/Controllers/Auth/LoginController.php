<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\Auth;

use App\Application\Auth\Login\Login;
use App\Application\Auth\Login\LoginInput;
use App\Infrastructure\Http\Requests\Auth\LoginRequest;
use App\Infrastructure\Http\Resources\Auth\LoginResource;

final readonly class LoginController
{
    public function __construct(
        private Login $login,
    ) {
    }

    public function __invoke(
        LoginRequest $request,
    ): LoginResource {
        $validated = $request->validated();

        $result = $this->login->execute(
            new LoginInput(
                email: (string) $validated['email'],
                password: (string) $validated['password'],
            )
        );

        return new LoginResource($result);
    }
}

?>