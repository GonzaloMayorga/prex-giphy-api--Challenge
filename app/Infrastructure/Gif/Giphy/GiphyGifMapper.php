<?php

declare(strict_types=1);

namespace App\Infrastructure\Gif\Giphy;

use App\Domain\Gif\Entities\Gif;
use UnexpectedValueException;

final class GiphyGifMapper
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function fromArray(array $payload): Gif
    {
        $id = $this->requiredString(
            value: $payload['id'] ?? null,
            field: 'id',
        );

        $title = $this->resolveTitle($payload);

        $originalUrl = $this->requiredString(
            value: $payload['images']['original']['url'] ?? null,
            field: 'images.original.url',
        );

        $previewUrl = $this->optionalString(
            $payload['images']['preview_gif']['url']
                ?? $payload['images']['fixed_width_small']['url']
                ?? null
        );

        $username = $this->optionalString(
            $payload['username'] ?? null
        );

        return new Gif(
            id: $id,
            title: $title,
            originalUrl: $originalUrl,
            previewUrl: $previewUrl,
            username: $username,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveTitle(array $payload): string
    {
        $title = $this->optionalString(
            $payload['title'] ?? null
        );

        if ($title !== null) {
            return $title;
        }

        $alternativeText = $this->optionalString(
            $payload['alt_text'] ?? null
        );

        if ($alternativeText !== null) {
            return $alternativeText;
        }

        return 'Untitled GIF';
    }

    private function requiredString(
        mixed $value,
        string $field,
    ): string {
        $normalizedValue = $this->optionalString($value);

        if ($normalizedValue === null) {
            throw new UnexpectedValueException(
                sprintf(
                    'The GIPHY field "%s" is required.',
                    $field,
                )
            );
        }

        return $normalizedValue;
    }

    private function optionalString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalizedValue = trim($value);

        return $normalizedValue === ''
            ? null
            : $normalizedValue;
    }
}
