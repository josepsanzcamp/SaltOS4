<?php

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects

// initial settings
ini_set('date.timezone', 'Europe/Madrid');

// maximum time
$maximum = 86400 * 7;

// compute information
$hashs = glob('*');
foreach ($hashs as $hash) {
    $hash = trim($hash);
    if (strlen($hash) != 32) {
        continue;
    }
    if (!file_exists("$hash/data/files/saltos.sqlite")) {
        continue;
    }
    $mtime = date('Y-m-d H:i:s', filemtime("$hash/data/files/saltos.sqlite"));
    $diff = time() - strtotime($mtime);
    if ($diff < $maximum) {
        continue;
    }
    $extra = '';
    $count = 0;
    while (file_exists("removed/$hash$extra")) {
        $count++;
        $extra = ".$count";
    }
    rename($hash, "removed/$hash$extra");
}

die();
