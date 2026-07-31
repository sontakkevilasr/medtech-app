<?php

/**
 * One-time helper to run `php artisan users:fix-doctor-names` on hosts with no terminal access.
 *
 * Strips a redundant "Dr."/"Doctor" honorific that got typed into a doctor's own
 * Full Name field, which otherwise renders twice (e.g. "Dr. Dr. Aparna Arya Tyagi")
 * since the app's templates already prepend "Dr." when displaying doctor names.
 *
 * Usage:
 *   1. Upload/deploy this file to the public/ folder (it's already there if you pushed this commit).
 *   2. Visit: https://smarterbusiness.in/fix-doctor-names.php?key=03c5ddddc7d6dcc64ed8005717d5d38a
 *   3. Confirm the output lists the doctor names that were cleaned.
 *   4. DELETE THIS FILE IMMEDIATELY AFTER — leaving it on a live server is a security risk.
 */

define('FIX_DOCTOR_NAMES_KEY', '03c5ddddc7d6dcc64ed8005717d5d38a');

if (($_GET['key'] ?? '') !== FIX_DOCTOR_NAMES_KEY) {
    http_response_code(403);
    echo 'Forbidden.';
    exit;
}

require __DIR__.'/../vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/plain');

Illuminate\Support\Facades\Artisan::call('users:fix-doctor-names');
echo Illuminate\Support\Facades\Artisan::output();

echo "\nDone. NOW DELETE THIS FILE (public/fix-doctor-names.php) FROM THE SERVER.\n";
