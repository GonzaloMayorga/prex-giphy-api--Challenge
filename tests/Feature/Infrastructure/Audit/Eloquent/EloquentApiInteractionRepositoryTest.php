<?php

declare(strict_types=1);

namespace Tests\Feature\Infrastructure\Audit\Eloquent;

use App\Domain\Audit\Entities\ApiInteraction;
use App\Infrastructure\Audit\Eloquent\ApiInteractionModel;
use App\Infrastructure\Audit\Eloquent\EloquentApiInteractionRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EloquentApiInteractionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_persists_an_api_interaction(): void
    {
        $user = User::factory()->create();

        $repository = new EloquentApiInteractionRepository;

        $repository->record(
            new ApiInteraction(
                userId: (int) $user->getKey(),
                service: 'api.gifs.search',
                httpMethod: 'GET',
                path: '/api/gifs',
                requestBody: [
                    'query' => [
                        'query' => 'cats',
                    ],
                ],
                responseStatus: 200,
                responseBody: [
                    'data' => [],
                ],
                originIp: '203.0.113.10',
                durationMs: 25,
            )
        );

        $this->assertDatabaseHas(
            'api_interaction_logs',
            [
                'user_id' => $user->getKey(),
                'service' => 'api.gifs.search',
                'http_method' => 'GET',
                'path' => '/api/gifs',
                'response_status' => 200,
                'origin_ip' => '203.0.113.10',
                'duration_ms' => 25,
            ],
        );

        $stored = ApiInteractionModel::query()
            ->firstOrFail();

        self::assertSame([
            'query' => [
                'query' => 'cats',
            ],
        ], $stored->request_body);

        self::assertSame([
            'data' => [],
        ], $stored->response_body);

        self::assertNotNull(
            $stored->created_at
        );
    }

    public function test_it_allows_an_unauthenticated_interaction(): void
    {
        $repository = new EloquentApiInteractionRepository;

        $repository->record(
            new ApiInteraction(
                userId: null,
                service: 'api.auth.login',
                httpMethod: 'POST',
                path: '/api/login',
                requestBody: [],
                responseStatus: 401,
                responseBody: [
                    'error' => [
                        'code' => 'INVALID_CREDENTIALS',
                    ],
                ],
                originIp: '203.0.113.10',
                durationMs: 15,
            )
        );

        $this->assertDatabaseHas(
            'api_interaction_logs',
            [
                'user_id' => null,
                'service' => 'api.auth.login',
                'response_status' => 401,
            ],
        );
    }
}
