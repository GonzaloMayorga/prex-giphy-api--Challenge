<?php

declare(strict_types=1);

namespace App\Application\Audit\Record;

use App\Domain\Audit\Entities\ApiInteraction;
use App\Domain\Audit\Ports\ApiInteractionRepository;

final readonly class RecordApiInteraction
{
    public function __construct(
        private ApiInteractionRepository $repository,
    ) {}

    public function execute(
        RecordApiInteractionInput $input,
    ): void {
        $interaction = new ApiInteraction(
            userId: $input->userId,
            service: $input->service,
            httpMethod: $input->httpMethod,
            path: $input->path,
            requestBody: $input->requestBody,
            responseStatus: $input->responseStatus,
            responseBody: $input->responseBody,
            originIp: $input->originIp,
            durationMs: $input->durationMs,
        );

        $this->repository->record(
            $interaction
        );
    }
}
