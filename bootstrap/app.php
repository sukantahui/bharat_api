<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Enable global CORS middleware
        $middleware->append(\Illuminate\Http\Middleware\HandleCors::class);
        
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // 401 Unauthenticated
        $exceptions->renderable(function (AuthenticationException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthenticated. Please provide a valid Bearer token.',
                    'data'    => null,
                ], 401);
            }
        });

        // 403 Forbidden / Unauthorized
        $exceptions->renderable(function (AuthorizationException|AccessDeniedHttpException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthorized action. You do not have permission to access this resource.',
                    'data'    => null,
                ], 403);
            }
        });

        // 404 Not Found
        $exceptions->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                // Function to extract model name from exception message
                $extractModelName = function ($exception) {
                    $message = $exception->getMessage();
                    if (preg_match('/No query results for model \[App\\\\Models\\\\(.+?)\]/', $message, $matches)) {
                        return $matches[1]; // Return model name, e.g., 'Employee'
                    }
                    return 'Resource'; // Default fallback
                };

                $modelName = $extractModelName($e);
                return response()->json([
                    'status'  => false,
                    'message' => "Requested {$modelName} or endpoint was not found.",
                    'data'    => null,
                ], 404);
            }
        });

        // 405 Method Not Allowed
        $exceptions->renderable(function (MethodNotAllowedHttpException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'HTTP method not allowed for this endpoint.',
                    'data'    => null,
                ], 405);
            }
        });
    })
    ->create();
