<?php

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects

function ob_passthru($cmd)
{
    ob_start();
    passthru("$cmd 2>&1");
    return ob_get_clean();
}

$files = glob('code/apps/*/locale/*/*.t2t');
foreach ($files as $file) {
    $file = str_replace('.t2t', '', $file);
    if (file_exists("$file.pdf") && filemtime("$file.t2t") <= filemtime("$file.pdf")) {
        continue;
    }
    ob_passthru("php scripts/makepdf.php $file.t2t");
}
