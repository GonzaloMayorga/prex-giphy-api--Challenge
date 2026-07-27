<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middleware;

use App\Application\Audit\Record\RecordApiInteraction;
use App\Application\Audit\Record\RecordApiInteractionInput;
use App\Infrastructure\Audit\Http\AuditContext;
use App\Infrastructure\Audit\Security\SensitiveDataRedactor;
use BackedEnum;
use Closure;
use DateTimeInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use JsonSerializable;
use Stringable;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final readonly class AuditApiInteractionMiddleware
{
    public function __construct(
        private RecordApiInteraction $recordInteraction,
        private SensitiveDataRedactor $redactor,
    ) {
    }

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(
        Request $request,
        Closure $next,
    ): Response {
        if ((bool) config('audit.enabled', true)) {
            $request->attributes->set(
                AuditContext::STARTED_AT_NANOSECONDS,
                hrtime(true),
            );
        }

        return $next($request);
    }

    public function terminate(
        Request $request,
        Response $response,
    ): void {
        if (!(bool) config('audit.enabled', true)) {
            return;
        }

        if (!$request->is('api', 'api/*')) {
            return;
        }

        try {
            $this->recordInteraction->execute(
                new RecordApiInteractionInput(
                    userId: $this->resolveUserId($request),
                    service: $this->resolveService($request),
                    httpMethod: $request->method(),
                    path: $request->path(),
                    requestBody: $this->requestPayload(
                        $request
                    ),
                    responseStatus: $response
                        ->getStatusCode(),
                    responseBody: $this->responsePayload(
                        $response
                    ),
                    originIp: $request->ip()
                        ?? '0.0.0.0',
                    durationMs: $this->durationMs(
                        $request
                    ),
                )
            );
        } catch (Throwable $exception) {
            Log::error(
                'API interaction audit could not be persisted.',
                [
                    'service' => $this->resolveService(
                        $request
                    ),
                    'method' => $request->method(),
                    'path' => $request->path(),
                    'exception' => $exception::class,
                    'message' => $exception->getMessage(),
                ],
            );
        }
    }

    private function resolveUserId(
        Request $request,
    ): ?int {
        $contextUserId = $request->attributes->get(
            AuditContext::USER_ID
        );

        if (
            is_numeric($contextUserId)
            && (int) $contextUserId > 0
        ) {
            return (int) $contextUserId;
        }

        $user = $request->user('api')
            ?? $request->user();

        if (!$user instanceof Authenticatable) {
            return null;
        }

        $identifier = $user->getAuthIdentifier();

        return is_numeric($identifier)
            && (int) $identifier > 0
                ? (int) $identifier
                : null;
    }

    private function resolveService(
        Request $request,
    ): string {
        $route = $request->route();

        if ($route !== null) {
            $routeName = $route->getName();

            if (
                is_string($routeName)
                && trim($routeName) !== ''
            ) {
                return $routeName;
            }
        }

        return sprintf(
            '%s /%s',
            mb_strtoupper($request->method()),
            $request->path(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function requestPayload(
        Request $request,
    ): array {
        $route = $request->route();

        $routeParameters = $route !== null
            ? $route->parameters()
            : [];

        $body = $request->isJson()
            ? $request->json()->all()
            : $request->request->all();

        return $this->sanitizeAndLimit([
            'query' => $request->query->all(),
            'body' => $body,
            'route_parameters' => $this->normalizeForJson(
                $routeParameters
            ),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function responsePayload(
        Response $response,
    ): array {
        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);

            return $this->sanitizeAndLimit(
                is_array($data)
                    ? $data
                    : ['value' => $data]
            );
        }

        try {
            $content = $response->getContent();
        } catch (Throwable) {
            return [
                '_unavailable' => true,
            ];
        }

        if ($content === false || $content === '') {
            return [];
        }

        $decoded = json_decode(
            $content,
            true,
        );

        if (json_last_error() === JSON_ERROR_NONE) {
            return $this->sanitizeAndLimit(
                is_array($decoded)
                    ? $decoded
                    : ['value' => $decoded]
            );
        }

        return $this->sanitizeAndLimit([
            'raw' => $content,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function sanitizeAndLimit(
        array $payload,
    ): array {
        $redacted = $this->redactor->redact(
            $payload
        );

        if (!is_array($redacted)) {
            $redacted = [
                'value' => $redacted,
            ];
        }

        $encoded = json_encode(
            $redacted,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_PARTIAL_OUTPUT_ON_ERROR,
        );

        if (!is_string($encoded)) {
            return [
                '_serialization_error' => true,
            ];
        }

        $maxPayloadBytes = max(
            1024,
            (int) config(
                'audit.max_payload_bytes',
                1048576,
            )
        );

        $actualBytes = strlen($encoded);

        if ($actualBytes <= $maxPayloadBytes) {
            return $redacted;
        }

        return [
            '_truncated' => true,
            '_original_bytes' => $actualBytes,
            'preview' => mb_strcut(
                $encoded,
                0,
                $maxPayloadBytes,
                'UTF-8',
            ),
        ];
    }

    private function normalizeForJson(
        mixed $value,
    ): mixed {
        if (
            $value === null
            || is_scalar($value)
        ) {
            return $value;
        }

        if (is_array($value)) {
            $normalized = [];

            foreach ($value as $key => $item) {
                $normalized[$key] = $this
                    ->normalizeForJson($item);
            }

            return $normalized;
        }

        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if ($value instanceof JsonSerializable) {
            return $this->normalizeForJson(
                $value->jsonSerialize()
            );
        }

        if ($value instanceof Stringable) {
            return (string) $value;
        }

        if (is_object($value)) {
            return [
                '_object_type' => $value::class,
            ];
        }

        return (string) $value;
    }

    private function durationMs(
        Request $request,
    ): int {
        $startedAt = $request->attributes->get(
            AuditContext::STARTED_AT_NANOSECONDS
        );

        if (!is_int($startedAt)) {
            return 0;
        }

        $elapsedNanoseconds = hrtime(true)
            -$startedAt;

        return max(
            0,
            (int) round(
                $elapsedNanoseconds / 1_000_000
            ),
        );
    }
}
?>
