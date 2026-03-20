<?php
// Run once then DELETE this file immediately!
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->call('key:generate');
$kernel->call('migrate', ['--force' => true]);
$kernel->call('storage:link');
$kernel->call('config:cache');
$kernel->call('route:cache');

echo "Done!";