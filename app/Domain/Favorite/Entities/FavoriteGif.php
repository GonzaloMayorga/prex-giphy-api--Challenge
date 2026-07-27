<?php

declare(strict_types=1);

namespace App\Domain\Favorite\Entities;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class FavoriteGif
{
    public const MAX_GIF_ID_LENGTH = 100;
    public const MAX_ALIAS_LENGTH = 100;

    private function __construct(
        private ?int $id,
        private int $userId,
        private string $gifId,
        private string $alias,
        private ?DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $updatedAt,
    ) {
        if ($this->id !== null && $this->id < 1) {
            throw new InvalidArgumentException(
                'The favorite ID must be greater than zero.'
            );
        }

        if ($this->userId < 1) {
            throw new InvalidArgumentException(
                'The favorite user ID must be greater than zero.'
            );
        }

        if ($this->gifId === '') {
            throw new InvalidArgumentException(
                'The favorite GIF ID cannot be empty.'
            );
        }

        if (
            mb_strlen($this->gifId)
            > self::MAX_GIF_ID_LENGTH
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'The favorite GIF ID cannot exceed %d characters.',
                    self::MAX_GIF_ID_LENGTH,
                )
            );
        }

        if ($this->alias === '') {
            throw new InvalidArgumentException(
                'The favorite alias cannot be empty.'
            );
        }

        if (
            mb_strlen($this->alias)
            > self::MAX_ALIAS_LENGTH
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'The favorite alias cannot exceed %d characters.',
                    self::MAX_ALIAS_LENGTH,
                )
            );
        }
    }

    public static function create(
        int $userId,
        string $gifId,
        string $alias,
    ): self {
        return new self(
            id: null,
            userId: $userId,
            gifId: self::normalizeGifId($gifId),
            alias: self::normalizeAlias($alias),
            createdAt: null,
            updatedAt: null,
        );
    }

    public static function reconstitute(
        int $id,
        int $userId,
        string $gifId,
        string $alias,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            id: $id,
            userId: $userId,
            gifId: self::normalizeGifId($gifId),
            alias: self::normalizeAlias($alias),
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function gifId(): string
    {
        return $this->gifId;
    }

    public function alias(): string
    {
        return $this->alias;
    }

    public function createdAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private static function normalizeGifId(
        string $gifId,
    ): string {
        return trim($gifId);
    }

    private static function normalizeAlias(
        string $alias,
    ): string {
        $normalizedAlias = preg_replace(
            '/\s+/u',
            ' ',
            trim($alias),
        );

        return is_string($normalizedAlias)
            ? $normalizedAlias
            : trim($alias);
    }
}

?>
