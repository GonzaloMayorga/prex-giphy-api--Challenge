<?php

declare(strict_types=1);

namespace App\Infrastructure\Audit\Eloquent;

use App\Domain\Audit\Entities\ApiInteraction;
use App\Domain\Audit\Exceptions\ApiInteractionRepositoryException;
use App\Domain\Audit\Ports\ApiInteractionRepository;
use Illuminate\Database\QueryException;

final class EloquentApiInteractionRepository implements
    ApiInteractionRepository
{
    public function record(
        ApiInteraction $interaction,
    ): void {
        try {
            ApiInteractionModel::query()->create([
                'user_id' => $interaction->userId(),
                'service' => $interaction->service(),
                'http_method' => $interaction->httpMethod(),
                'path' => $interaction->path(),
                'request_body' => $interaction->requestBody(),
                'response_status' => $interaction
                    ->responseStatus(),
                'response_body' => $interaction->responseBody(),
                'origin_ip' => $interaction->originIp(),
                'duration_ms' => $interaction->durationMs(),
            ]);
        } catch (QueryException $exception) {
            throw ApiInteractionRepositoryException
                ::persistenceFailed(
                    $exception
                );
        }
    }
}

?>
