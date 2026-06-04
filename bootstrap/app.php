<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckArea;
use App\Http\Middleware\ForcePasswordChange;
use App\Http\Middleware\RequireApprover;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\DataHorizonMiddleware::class,
        ]);
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'area' => CheckArea::class,
            'approver' => RequireApprover::class,
            'force_password_change' => ForcePasswordChange::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
