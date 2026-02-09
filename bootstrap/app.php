<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('api')
                ->group(base_path('routes/api.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle API exceptions with JSON response
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->is('api/*') || $request->is('meetflow/api/*')) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->is('api/*') || $request->is('meetflow/api/*')) {
                return response()->json([
                    'message' => 'Resource not found',
                ], 404);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException $e, $request) {
            if ($request->is('api/*') || $request->is('meetflow/api/*')) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 401);
            }
        });

        $exceptions->render(function (\App\Exceptions\AIServiceException $e, $request) {
            if ($request->is('api/*') || $request->is('meetflow/api/*')) {
                return response()->json([
                    'message' => 'AI Service Error',
                    'error' => $e->getMessage(),
                ], 500);
            }
        });

        $exceptions->render(function (\App\Exceptions\MeetingProcessingException $e, $request) {
            if ($request->is('api/*') || $request->is('meetflow/api/*')) {
                return response()->json([
                    'message' => 'Meeting Processing Error',
                    'error' => $e->getMessage(),
                ], 500);
            }
        });
    })->create();
