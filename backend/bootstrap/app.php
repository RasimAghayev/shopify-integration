<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders()
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        health: '/api/health',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle Method Not Allowed for API routes
        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                $allowedMethods = $e->getHeaders()['Allow'] ?? 'Unknown';

                return response()->json([
                    'error' => 'Method Not Allowed',
                    'message' => "The {$request->method()} method is not supported for this route.",
                    'allowedMethods' => explode(', ', $allowedMethods),
                ], 405);
            }

            return $e;
        });

        // Handle Not Found for API routes
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Not Found',
                    'message' => 'The requested resource was not found.',
                ], 404);
            }
            return $e;
        });
    })->create();
