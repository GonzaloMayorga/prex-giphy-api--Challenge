<?php

declare(strict_types=1);

namespace Tests\Feature\Infrastructure\Http;

use Tests\TestCase;

final class ApiErrorResponseTest extends TestCase
{
    public function test_it_returns_json_for_an_unknown_api_route(): void
    {
        $response = $this->getJson(
            '/api/unknown-endpoint'
        );

        $response
            ->assertNotFound()
            ->assertExactJson([
                'message' => 'The requested API endpoint was not found.',
                'error' => [
                    'code' => 'ROUTE_NOT_FOUND',
                ],
            ]);
    }

    public function test_it_returns_method_not_allowed_as_json(): void
    {
        $response = $this->postJson(
            '/api/gifs'
        );

        $response
            ->assertStatus(405)
            ->assertExactJson([
                'message' => 'The HTTP method is not allowed for this endpoint.',
                'error' => [
                    'code' => 'METHOD_NOT_ALLOWED',
                ],
            ]);
    }
}