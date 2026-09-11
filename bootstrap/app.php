<?php

use App\Http\Middleware\CaptureAdContext;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Records gclid / utm_* behind every page view, so attribution
        // survives the hop from the landing page to the thank-you page.
        $middleware->web(append: [
            CaptureAdContext::class,
        ]);

        // There is no public login page — 'login' means the dashboard.
        $middleware->redirectGuestsTo(fn () => route('dashboard.login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
