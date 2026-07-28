<?php

declare(strict_types=1);

namespace Tests\Fakes\Auth;

use App\Domain\Auth\Ports\AccessTokenIssuer;
use App\Domain\Auth\ValueObjects\IssuedAccessToken;
use LogicException;

final class FakeAccessTokenIssuer implements AccessTokenIssuer
{
    private ?IssuedAccessToken $token = null;

    public ?int $receivedUserId = null;

    public ?string $receivedTokenName = null;

    public function willIssue(
        IssuedAccessToken $token,
    ): void {
        $this->token = $token;
    }

    public function issue(
        int $userId,
        string $tokenName,
    ): IssuedAccessToken {
        $this->receivedUserId = $userId;
        $this->receivedTokenName = $tokenName;

        if ($this->token === null) {
            throw new LogicException(
                'No token was configured in FakeAccessTokenIssuer.'
            );
        }

        return $this->token;
    }
}
