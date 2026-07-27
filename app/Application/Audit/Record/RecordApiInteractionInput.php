<?php

declare(strict_types=1);

namespace App\Application\Audit\Record;

final readonly class RecordApiInteractionInput
{
    public string $service;
    public string $httpMethod;
    public string $path;

    /**
     * @param array<string, mixed> $requestBody
     * @param array<string, mixed> $responseBody
     */
    public function __construct(
        public ?int $userId,
        string $service,
        string $httpMethod,
        string $path,
        public array $requestBody,
        public int $responseStatus,
        public array $responseBody,
        public string $originIp,
        public int $durationMs,
    ) {
        $this->service = trim($service);
        $this->httpMethod = mb_strtoupper(
            trim($httpMethod)
        );

        $normalizedPath = trim($path);

        $this->path = '/'
            .ltrim($normalizedPath, '/');
    }
}

?>
