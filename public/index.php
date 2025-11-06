<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

// Prevent PHP from advertising itself via the X-Powered-By header. Also
// attempt to disable expose_php at runtime as a best-effort measure.
@ini_set('expose_php', '0');
if (function_exists('header_remove')) {
    @header_remove('X-Powered-By');
}

$app->handleRequest(Request::capture());
