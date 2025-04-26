<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\UniqueConstraintViolationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(\App\Http\Middleware\SetLocale::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e) {
            if (request()->is('api/*')) {
                return response()->json([
                    'message' => __('auth.messages.unauthenticated'),
                    'status' => 'error',
                    'data' => null
                ], Response::HTTP_UNAUTHORIZED);
            }
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e) {
            if (request()->is('api/*')) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                    'status' => 'error',
                    'data' => null
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            if (request()->is('api/*')) {
                return response()->json([
                    'message' => 'Resource not found',
                    'status' => 'error',
                    'data' => null
                ], Response::HTTP_NOT_FOUND);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e) {
            if (request()->is('api/*')) {
                return response()->json([
                    'message' => 'Method not allowed',
                    'status' => 'error',
                    'data' => null
                ], Response::HTTP_METHOD_NOT_ALLOWED);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            if (request()->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This action is unauthorized.',
                    'data' => null
                ], Response::HTTP_FORBIDDEN);
            }
        });

        $exceptions->render(function (UniqueConstraintViolationException $e) {
            if (request()->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'A record with this information already exists.',
                    'data' => null
                ], Response::HTTP_CONFLICT);
            }
        });

        // Add a fallback handler for all other exceptions
        $exceptions->render(function (\Throwable $e) {
            if (request()->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'data' => null
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });
    })->create();
