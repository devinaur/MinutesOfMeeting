<?php

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Console\ServeCommand;
use Illuminate\Http\Request;

// Ensure Microsoft ODBC driver is found on macOS (Homebrew unixODBC)
if (file_exists('/opt/homebrew/etc/odbcinst.ini')) {
    if (! getenv('ODBCSYSINI')) {
        putenv('ODBCSYSINI=/opt/homebrew/etc');
        $_ENV['ODBCSYSINI'] = '/opt/homebrew/etc';
        $_SERVER['ODBCSYSINI'] = '/opt/homebrew/etc';
    }
    if (class_exists(ServeCommand::class)) {
        ServeCommand::$passthroughVariables[] = 'ODBCSYSINI';
    }
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
