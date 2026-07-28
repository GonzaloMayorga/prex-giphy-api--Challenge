<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Resources\Auth;

use App\Application\Auth\Login\LoginResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use LogicException;

final class LoginResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (! $this->resource instanceof LoginResult) {
            throw new LogicException(
                'LoginResource expects a LoginResult instance.'
            );
        }

        $user = $this->resource->user();
        $token = $this->resource->token();

        return [
            'access_token' => $token->accessToken(),
            'token_type' => $token->tokenType(),
            'expires_in' => $token->expiresIn(),
            'expires_at' => $token
                ->expiresAt()
                ->format(DATE_ATOM),
            'user' => [
                'id' => $user->id(),
                'name' => $user->name(),
                'email' => $user->email(),
            ],
        ];
    }
}
