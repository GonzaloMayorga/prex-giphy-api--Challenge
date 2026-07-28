<?php

declare(strict_types=1);

namespace Tests\Feature\Infrastructure\Http\Middleware;

use App\Application\Audit\Record\RecordApiInteraction;
use App\Infrastructure\Audit\Http\AuditContext;
use App\Infrastructure\Audit\Security\SensitiveDataRedactor;
use App\Infrastructure\Http\Middleware\AuditApiInteractionMiddleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Tests\Fakes\Audit\FakeApiInteractionRepository;
use Tests\TestCase;

final class AuditApiInteractionMiddlewareTest extends TestCase
{
    public function test_it_audits_and_redacts_an_api_response(): void
    {
        config([
            'audit.enabled' => true,
            'audit.max_payload_bytes' => 1048576,
        ]);

        $repository = new FakeApiInteractionRepository;

        $middleware = new AuditApiInteractionMiddleware(
            recordInteraction: new RecordApiInteraction(
                $repository
            ),
            redactor: new SensitiveDataRedactor([
                'password',
                'access_token',
            ]),
        );

        $request = Request::create(
            '/api/login',
            'POST',
            [],
            [],
            [],
            [
                'REMOTE_ADDR' => '203.0.113.10',
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            json_encode([
                'email' => 'challenge@example.com',
                'password' => 'secret-password',
            ], JSON_THROW_ON_ERROR),
        );

        $route = new Route(
            ['POST'],
            'api/login',
            static fn () => null,
        );

        $route->name('api.auth.login');

        $route->bind($request);

        $request->setRouteResolver(
            static fn () => $route
        );

        $request->attributes->set(
            AuditContext::USER_ID,
            10,
        );

        $response = new JsonResponse([
            'data' => [
                'access_token' => 'secret-token',
                'token_type' => 'Bearer',
                'user' => [
                    'id' => 10,
                    'email' => 'challenge@example.com',
                ],
            ],
        ], 200);

        $returnedResponse = $middleware->handle(
            $request,
            static fn () => $response,
        );

        self::assertSame(
            $response,
            $returnedResponse,
        );

        $middleware->terminate(
            $request,
            $response,
        );

        $interaction = $repository
            ->recordedInteraction;

        self::assertNotNull($interaction);

        self::assertSame(
            10,
            $interaction->userId(),
        );

        self::assertSame(
            'api.auth.login',
            $interaction->service(),
        );

        self::assertSame(
            'POST',
            $interaction->httpMethod(),
        );

        self::assertSame(
            '/api/login',
            $interaction->path(),
        );

        self::assertSame(
            '[REDACTED]',
            $interaction
                ->requestBody()['body']['password'],
        );

        self::assertSame(
            '[REDACTED]',
            $interaction
                ->responseBody()['data']['access_token'],
        );

        self::assertSame(
            200,
            $interaction->responseStatus(),
        );

        self::assertSame(
            '203.0.113.10',
            $interaction->originIp(),
        );

        self::assertGreaterThanOrEqual(
            0,
            $interaction->durationMs(),
        );
    }

    public function test_it_does_not_record_when_audit_is_disabled(): void
    {
        config([
            'audit.enabled' => false,
        ]);

        $repository = new FakeApiInteractionRepository;

        $middleware = new AuditApiInteractionMiddleware(
            recordInteraction: new RecordApiInteraction(
                $repository
            ),
            redactor: new SensitiveDataRedactor([
                'password',
            ]),
        );

        $request = Request::create(
            '/api/login',
            'POST',
            [],
            [],
            [],
            [
                'REMOTE_ADDR' => '203.0.113.10',
            ],
        );

        $response = new JsonResponse(
            [],
            200,
        );

        $middleware->handle(
            $request,
            static fn () => $response,
        );

        $middleware->terminate(
            $request,
            $response,
        );

        self::assertSame(
            0,
            $repository->recordCalls,
        );
    }
}
