<?php

declare(strict_types=1);

namespace App\Domain\Gif\Exceptions;

use RuntimeException;

final class GifNotFoundException extends RuntimeException
{
    public static function withId(string $id): self
    {
        return new self(message: sprintf('The Gif with ID "%s" was not found.', $id));
    }
}
