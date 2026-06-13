<?php

use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\SetLocale;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(append: [
            SetLocale::class,
        ]);
        $middleware->alias([
            'account.active' => EnsureAccountIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            return response()->json(['message' => __('messages.unauthenticated')], 401);
        });
        $exceptions->render(function (AuthorizationException $e, Request $request) {
            return response()->json(['message' => __('messages.forbidden')], 403);
        });
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $model = class_basename($e->getModel());
                $ids = implode(', ', $e->getIds());

                return response()->json([
                    'message' => "{$model} with ID [{$ids}] was not found.",
                ], 404);
            }
        });
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $previous = $e->getPrevious();

                if ($previous instanceof ModelNotFoundException) {
                    $model = class_basename($previous->getModel());
                    $ids = implode(', ', $previous->getIds());

                    return response()->json([
                        'message' => "{$model} with ID [{$ids}] was not found.",
                    ], 404);
                }

                return response()->json(['message' => __('messages.not_found')], 404);
            }
        });
        $exceptions->render(function (QueryException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                if (str_contains($e->getMessage(), '22P02')) {
                    return response()->json([
                        'message' => 'The provided ID is not a valid UUID.',
                    ], 422);
                }
            }
        });
    })->create();
