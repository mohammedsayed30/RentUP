<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
       
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'ability' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
            'abilities' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        
        $exceptions->renderable(function (NotFoundHttpException $e, $request) {
            // Check if the request is for the API
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'The requested route does not exist on this API.',
                    'error' => 'Not Found'
                ], 404);
            }
        });

        
        $exceptions->renderable(function (MethodNotAllowedHttpException $e, $request) {
            // Check if the request is for the API
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'The method you are using is not supported for this route.',
                    'error' => 'Method Not Allowed',
                ], 405);
            }
        });
    })->create();
