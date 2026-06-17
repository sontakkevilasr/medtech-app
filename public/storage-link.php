<?php

/**
 * One-time helper to run `php artisan storage:link` on hosts with no terminal access.
 *
 * Usage:
 *   1. Upload/deploy this file to the public/ folder (it's already there if you pushed this commit).
 *   2. Visit: https://smarterbusiness.in/storage-link.php?key=494bd99c553d6b09959567a4cb0e7a2b
 *   3. Confirm the output says the link was created.
 *   4. DELETE THIS FILE IMMEDIATELY AFTER — leaving it on a live server is a security risk.
 */

define('STORAGE_LINK_KEY', '494bd99c553d6b09959567a4cb0e7a2b');

if (($_GET['key'] ?? '') !== STORAGE_LINK_KEY) {
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

$target = storage_path('app/public');
$link   = public_path('storage');

echo "Target: {$target}\n";
echo "Link:   {$link}\n\n";

try {
    Illuminate\Support\Facades\Artisan::call('storage:link', ['--force' => true]);
    echo Illuminate\Support\Facades\Artisan::output();
} catch (\Throwable $e) {
    echo "storage:link failed: {$e->getMessage()}\n";
    echo "Falling back to a manual recursive copy (use this if symlink() is disabled on this host)...\n\n";

    if (is_dir($link) && !is_link($link)) {
        // Already a real directory (likely from a previous fallback run) — just sync into it.
    } elseif (file_exists($link) || is_link($link)) {
        @unlink($link);
    } else {
        mkdir($link, 0755, true);
    }

    $copy = function (string $from, string $to) use (&$copy) {
        if (!is_dir($to)) {
            mkdir($to, 0755, true);
        }
        foreach (scandir($from) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $fromPath = "{$from}/{$item}";
            $toPath   = "{$to}/{$item}";
            if (is_dir($fromPath)) {
                $copy($fromPath, $toPath);
            } else {
                copy($fromPath, $toPath);
            }
        }
    };

    $copy($target, $link);
    echo "Manual copy complete. Note: this is a snapshot, not a live link — re-run this script after every\n";
    echo "new file upload (logo/photo/etc.) until symlink() is enabled on this host.\n";
}

echo "\nDone. NOW DELETE THIS FILE (public/storage-link.php) FROM THE SERVER.\n";
