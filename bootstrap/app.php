<?php

use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use App\Http\Middleware\ApiAuthenticate;
use Illuminate\Validation\ValidationException;
use App\Http\Middleware\Seller\CheckSellerStatus;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();

        $middleware->alias([
        'seller.checkStatus' => CheckSellerStatus::class,
    ]);
    })
     ->withExceptions(function (Exceptions $exceptions) {
        // Custom rendering for validation exceptions
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson()) {
                $formattedErrors = [];
                foreach ($e->errors() as $field => $messages) {
                    $formattedErrors[] = [
                        'field'    => $field,
                        'messages' => $messages,
                    ];
                }

                return response()->json([
                    'error' => [
                        'status_code'    => $e->status,
                        'errors'         => $formattedErrors,
                        'global_message' => 'Validation failed for one or more fields.',
                    ],
                ], $e->status);
            }
            // For non-JSON requests, use Laravel's default behavior
            return null;
        });
    })->create();