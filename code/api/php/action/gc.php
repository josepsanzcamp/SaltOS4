<?php

/**
 *  ____        _ _    ___  ____    _  _    _
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / |
 * \___ \ / _` | | __| | | \___ \  | || |_ | |
 *  ___) | (_| | | |_| |_| |___) | |__   _|| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)_|
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

if (!semaphore_acquire('gc')) {
    show_php_error(['phperror' => 'Could not acquire the semaphore']);
}

db_connect();
require_once 'php/lib/upload.php';
require_once 'php/lib/trash.php';
require_once 'php/lib/gc.php';

$time1 = microtime(true);
$output1 = gc_upload();
$time2 = microtime(true);
$output2 = gc_trash();
$time3 = microtime(true);
$output3 = gc_exec();
$time4 = microtime(true);

semaphore_release('gc');
output_handler_json([
    'gc_upload' => [
        'time' => round($time2 - $time1, 6),
        'deleted' => $output1,
        'count' => count($output1),
    ],
    'gc_trash' => [
        'time' => round($time3 - $time2, 6),
        'deleted' => $output2,
        'count' => count($output2),
    ],
    'gc_exec' => [
        'time' => round($time4 - $time3, 6),
        'deleted' => $output3,
        'count' => count($output3),
    ],
]);
