<?php

use App\Http\Middleware\TrackUserActivity;
use App\Jobs\CleanOldTimeSlots;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
//        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(function (Request $request) {
            return $request->expectsJson() || $request->is('api/*')
                ? null
                : config('app.url');
        });

        $middleware->api(append: [
            TrackUserActivity::class,
        ]);

        $middleware->alias([
            'verified.api' => App\Http\Middleware\EnsureEmailIsVerifiedForApi::class
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->job(new CleanOldTimeSlots)->dailyAt('02:00');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(function (Request $request) {
            return $request->expectsJson() || $request->is('api/*');
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        });

        return $exceptions;
    })->create();
