<?php

/**
 *  ____        _ _    ___  ____  _  _
 * / ___|  __ _| | |_ / _ \/ ___|| || |
 * \___ \ / _` | | __| | | \___ \| || |_
 *  ___) | (_| | | |_| |_| |___) |__   _|
 * |____/ \__,_|_|\__|\___/|____/   |_|
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
 */

declare(strict_types=1);

/**
 * Garbage Collector action
 *
 * This action executes the gc_exec function in the gc.php library, the execution
 * of this accion only is allowed from the command line
 */

if (!get_data('server/xuid')) {
    show_php_error(['phperror' => 'Permission denied']);
}

if (!semaphore_acquire('cron')) {
    show_php_error(['phperror' => 'Could not acquire the semaphore']);
}

db_connect();
require_once 'php/lib/cron.php';

$time1 = microtime(true);
$total1 = cron_gc();
$time2 = microtime(true);
$total2 = cron_exec();
$time3 = microtime(true);

semaphore_release('cron');
output_handler_json([
    'cron_gc' => [
        'time' => round($time2 - $time1, 6),
        'total' => $total1,
    ],
    'cron_exec' => [
        'time' => round($time3 - $time2, 6),
        'total' => $total2,
    ],
]);
