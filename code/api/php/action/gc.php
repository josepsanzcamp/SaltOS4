<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2024 by Josep Sanz Campderrós
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

if (get_data("server/request_method") != "CLI") {
    show_php_error(["phperror" => "Permission denied"]);
}

if (!semaphore_acquire("gc")) {
    show_php_error(["phperror" => "Could not acquire the semaphore"]);
}

db_connect();
require_once "php/lib/gc.php";
require_once "php/lib/upload.php";
$time1 = microtime(true);
$output1 = gc_upload();
$time2 = microtime(true);
$output2 = gc_exec();
$time3 = microtime(true);
semaphore_release('gc');
output_handler([
    'data' => json_encode([
        'gc_upload' => array_merge([
            'time' => sprintf('%f', $time2 - $time1),
        ], $output1),
        'gc_exec' => array_merge([
            'time' => sprintf('%f', $time3 - $time2),
        ], $output2),
    ], JSON_PRETTY_PRINT) . PHP_EOL,
    'type' => 'application/json',
    'cache' => false,
]);
