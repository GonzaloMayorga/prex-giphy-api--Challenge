<?php

declare(strict_types=1);

namespace App\Domain\Audit\Entities;

use InvalidArgumentException;

final readonly class ApiInteraction
{
    public const MAX_SERVICE_LENGTH = 150;
    public const MAX_HTTP_METHOD_LENGTH = 10;
    public const MAX_PATH_LENGTH = 2048;

    /**
     * @param array<string, mixed> $requestBody
     * @param array<string, mixed> $responseBody
     */
    public function __construct(
        private ?int $userId,
        private string $service,
        private string $httpMethod,
        private string $path,
        private array $requestBody,
        private int $responseStatus,
        private array $responseBody,
        private string $originIp,
        private int $durationMs,
    ) {
        if ($this->userId !== null && $this->userId < 1) {
            throw new InvalidArgumentException(
                'The audit user ID must be greater than zero.'
            );
        }

        if (trim($this->service) === '') {
            throw new InvalidArgumentException(
                'The audited service cannot be empty.'
            );
        }

        if (
            mb_strlen($this->service)
            > self::MAX_SERVICE_LENGTH
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'The audited service cannot exceed %d characters.',
                    self::MAX_SERVICE_LENGTH,
                )
            );
        }

        if (
            trim($this->httpMethod) === ''
            || preg_match('/^[A-Z]+$/', $this->httpMethod) !== 1
        ) {
            throw new InvalidArgumentException(
                'The audited HTTP method must be valid.'
            );
        }

        if (
            mb_strlen($this->httpMethod)
            > self::MAX_HTTP_METHOD_LENGTH
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'The HTTP method cannot exceed %d characters.',
                    self::MAX_HTTP_METHOD_LENGTH,
                )
            );
        }

        if (trim($this->path) === '') {
            throw new InvalidArgumentException(
                'The audited path cannot be empty.'
            );
        }

        if (
            mb_strlen($this->path)
            > self::MAX_PATH_LENGTH
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'The audited path cannot exceed %d characters.',
                    self::MAX_PATH_LENGTH,
                )
            );
        }

        if (
            $this->responseStatus < 100
            || $this->responseStatus > 599
        ) {
            throw new InvalidArgumentException(
                'The audited response status must be between 100 and 599.'
            );
        }

        if (
            filter_var(
                $this->originIp,
                FILTER_VALIDATE_IP,
            ) === false
        ) {
            throw new InvalidArgumentException(
                'The audited origin IP must be valid.'
            );
        }

        if ($this->durationMs < 0) {
            throw new InvalidArgumentException(
                'The audited duration cannot be negative.'
            );
        }
    }

    public function userId(): ?int
    {
        return $this->userId;
    }

    public function service(): string
    {
        return $this->service;
    }

    public function httpMethod(): string
    {
        return $this->httpMethod;
    }

    public function path(): string
    {
        return $this->path;
    }

    /**
     * @return array<string, mixed>
     */
    public function requestBody(): array
    {
        return $this->requestBody;
    }

    public function responseStatus(): int
    {
        return $this->responseStatus;
    }

    /**
     * @return array<string, mixed>
     */
    public function responseBody(): array
    {
        return $this->responseBody;
    }

    public function originIp(): string
    {
        return $this->originIp;
    }

    public function durationMs(): int
    {
        return $this->durationMs;
    }
}

?>
