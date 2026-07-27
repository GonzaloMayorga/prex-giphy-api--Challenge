<?php

declare(strict_types=1);

namespace App\Application\Gif\GetById;

use InvalidArgumentException;

final readonly class GetGifByIdInput
{
    public string $id;

    public function __construct(string $id)
    {
        $normalizedId = trim($id);

        if ($normalizedId === '') {
            throw new InvalidArgumentException(
                'The GIF ID cannot be empty.'
            );
        }

        if (mb_strlen($normalizedId) > 100) {
            throw new InvalidArgumentException(
                'The GIF ID cannot exceed 100 characters.'
            );
        }

        $this->id = $normalizedId;
    }
}
