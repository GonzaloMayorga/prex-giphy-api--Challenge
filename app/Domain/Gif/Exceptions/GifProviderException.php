<?php

declare(strict_types=1);

namespace App\Domain\Gif\Exceptions;

use RuntimeException;
use Throwable;

final class GifProviderException extends RuntimeException
{
    public static function connectionFailed(
        Throwable $previous,
    ): self {
        return new self(
            message: 'The GIF provider could not be reached.',
            previous: $previous,
        );
    }

    public static function requestFailed(int $statusCode, ?string $providerMessage = null): self
    {
        $message = sprintf('The Gif provider returned an error with status code %d.', $statusCode);

        if ($providerMessage !== null && trim($providerMessage) !== '') {
            $message .= ' Provider message: '.trim($providerMessage);
        }

        return new self($message);
    }

    public static function invalidResponse(string $reason, ?Throwable $previous = null): self
    {
        return new self(message: sprintf('The Gif provider returned an invalid response: %s', $reason), previous: $previous);
    }
}
