<?php
declare(strict_types=1);

namespace App\Domain\Gif\Entities;

final readonly class Gif
{
    public function __construct(
        private string $id,
        private string $title,
        private string $originalUrl,
        private ?string $previewUrl = null,
        private ?string $username = null,
    ) {
        if (trim($this->id) === '') {
            throw new \InvalidArgumentException('Gif id cannot be empty.');
        }

        if (trim($this->title) === '') {
            throw new \InvalidArgumentException('Gif title cannot be empty.');
        }

        if (filter_var($this->originalUrl, FILTER_VALIDATE_URL) === false) {
            throw new \InvalidArgumentException('Gif original URL is not valid.');
        }

        if ($this->previewUrl !== null && filter_var($this->previewUrl, FILTER_VALIDATE_URL) === false) {
            throw new \InvalidArgumentException('Gif preview URL is not valid.');
        }
    }

    public function id(): string
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function originalUrl(): string
    {
        return $this->originalUrl;
    }

    public function previewUrl(): ?string
    {
        return $this->previewUrl;
    }

    public function username(): ?string
    {
        return $this->username;
    }
}
?>