<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Laravel registra aquí los grupos y alias de middleware predeterminados.
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Laravel registra aquí el manejador de excepciones de la aplicación.
    })
    ->create();
