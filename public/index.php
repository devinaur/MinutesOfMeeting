<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Ensure Microsoft ODBC driver configuration is detected on macOS
if (! getenv('ODBCSYSINI') && file_exists('/opt/homebrew/etc/odbcinst.ini')) {
    putenv('ODBCSYSINI=/opt/homebrew/etc');
    $_ENV['ODBCSYSINI'] = '/opt/homebrew/etc';
    $_SERVER['ODBCSYSINI'] = '/opt/homebrew/etc';
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
