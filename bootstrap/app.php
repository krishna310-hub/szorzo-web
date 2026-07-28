<?php

use App\Http\Middleware\admin;
use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\EnsureRevenueAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
        'admin' => admin::class,
        'maintenance' => CheckMaintenanceMode::class,
        'revenue.admin' => EnsureRevenueAdmin::class,
    ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
