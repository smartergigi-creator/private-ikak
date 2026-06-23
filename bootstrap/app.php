<?php

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
            'karate.auth' => \App\Http\Middleware\KarateAuth::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'can.upload' => \App\Http\Middleware\CanUpload::class,
            'can.share' => \App\Http\Middleware\CanShare::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
