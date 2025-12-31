<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2026 by Josep Sanz Campderrós
 * More information in https://www.saltos.org or info@saltos.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
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
