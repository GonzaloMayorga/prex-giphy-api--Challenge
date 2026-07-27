<?php

declare(strict_types=1);

use App\Domain\Gif\Exceptions\GifNotFoundException;
use App\Domain\Gif\Exceptions\GifProviderException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Domain\Auth\Exceptions\InvalidCredentialsException;
use Illuminate\Auth\AuthenticationException;

return Application::configure(
    basePath: dirname(__DIR__)
)
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(
        function (Middleware $middleware): void {
        }
    )
    ->withExceptions(
        function (Exceptions $exceptions): void {
            $exceptions->shouldRenderJsonWhen(
                function (
                    Request $request,
                    Throwable $exception,
                ): bool {
                    return $request->is('api', 'api/*')
                        || $request->expectsJson();
                }
            );

            $exceptions->render(
                function (
                    ValidationException $exception,
                    Request $request,
                ): ?JsonResponse {
                    if (!$request->is('api', 'api/*')) {
                        return null;
                    }

                    return response()->json([
                        'message' => 'The request contains invalid data.',
                        'error' => [
                            'code' => 'VALIDATION_ERROR',
                            'details' => $exception->errors(),
                        ],
                    ], 422);
                }
            );
            
            $exceptions->render(
                function (
                    InvalidCredentialsException $exception,
                    Request $request,
                ): ?JsonResponse {
                    if (!$request->is('api', 'api/*')) {
                        return null;
                    }

                    return response()->json([
                        'message' => 'The provided credentials are invalid.',
                        'error' => [
                            'code' => 'INVALID_CREDENTIALS',
                        ],
                    ], 401);
                }
            );

            $exceptions->render(
                function (
                    AuthenticationException $exception,
                    Request $request,
                ): ?JsonResponse {
                    if (!$request->is('api', 'api/*')) {
                        return null;
                    }

                    return response()->json([
                        'message' => 'Authentication is required to access this resource.',
                        'error' => [
                            'code' => 'UNAUTHENTICATED',
                        ],
                    ], 401);
                }
            );

            $exceptions->render(
                function (
                    GifNotFoundException $exception,
                    Request $request,
                ): ?JsonResponse {
                    if (!$request->is('api', 'api/*')) {
                        return null;
                    }

                    return response()->json([
                        'message' => $exception->getMessage(),
                        'error' => [
                            'code' => 'GIF_NOT_FOUND',
                        ],
                    ], 404);
                }
            );

            $exceptions->render(
                function (
                    GifProviderException $exception,
                    Request $request,
                ): ?JsonResponse {
                    if (!$request->is('api', 'api/*')) {
                        return null;
                    }

                    return response()->json([
                        'message' => 'The GIF provider is temporarily unavailable.',
                        'error' => [
                            'code' => 'GIF_PROVIDER_UNAVAILABLE',
                        ],
                    ], 502);
                }
            );

            $exceptions->render(
                function (
                    NotFoundHttpException $exception,
                    Request $request,
                ): ?JsonResponse {
                    if (!$request->is('api', 'api/*')) {
                        return null;
                    }

                    return response()->json([
                        'message' => 'The requested API endpoint was not found.',
                        'error' => [
                            'code' => 'ROUTE_NOT_FOUND',
                        ],
                    ], 404);
                }
            );

            $exceptions->render(
                function (
                    MethodNotAllowedHttpException $exception,
                    Request $request,
                ): ?JsonResponse {
                    if (!$request->is('api', 'api/*')) {
                        return null;
                    }

                    return response()->json([
                        'message' => 'The HTTP method is not allowed for this endpoint.',
                        'error' => [
                            'code' => 'METHOD_NOT_ALLOWED',
                        ],
                    ], 405);
                }
            );
        }
    )
    ->create();
