<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2025 by Josep Sanz Campderrós
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
 * DB Schema action
 *
 * This action executes the db_schema and db_static functions in the dbschema.php
 * library, the execution of this accion only is allowed from the command line
 */

if (!get_data('server/xuid')) {
    show_php_error(['phperror' => 'Permission denied']);
}

if (!semaphore_acquire('setup')) {
    show_php_error(['phperror' => 'Could not acquire the semaphore']);
}

db_connect();
require_once 'php/lib/dbschema.php';
require_once 'php/lib/setup.php';

// This part allow request like setup/crm for specific setups
if (get_data('rest/1')) {
    $dir = encode_bad_chars(strval(get_data('rest/1')));

    // Search for specific setup
    $file = "apps/$dir/sample/setup.php";
    if (file_exists($file)) {
        require_once $file;
        $output = "__setup_helper_$dir"();
        semaphore_release('setup');
        output_handler_json($output);
    }

    // Generic setup for .sql.gz files
    $output = __setup_helper($dir);
    semaphore_release('setup');
    output_handler_json($output);
}

$dbschema_check = __dbschema_check();
$dbschema_hash = __dbschema_hash();
$dbstatic_check = __dbstatic_check();
$dbstatic_hash = __dbstatic_hash();

$time0 = microtime(true);
$output0 = check_system();
$time1 = microtime(true);
$output1 = check_directories();
$time2 = microtime(true);
$output2 = check_composer();
$time3 = microtime(true);
$errors = array_filter(array_merge($output0, $output1, $output2), function ($x) {
    return isset($x['error']);
});
if (count($errors)) {
    semaphore_release('setup');
    output_handler_json([
        'system' => [
            'time' => round($time1 - $time0, 6),
            'output' => $output0,
            'count' => count($output0),
        ],
        'directories' => [
            'time' => round($time2 - $time1, 6),
            'output' => $output1,
            'count' => count($output1),
        ],
        'composer' => [
            'time' => round($time3 - $time2, 6),
            'output' => $output2,
            'count' => count($output2),
        ],
    ]);
}
$output3 = db_schema();
$time4 = microtime(true);
$output4 = db_static();
$total4 = 0;
foreach ($output4 as $key => $val) {
    $from = $val['from'];
    $to = $val['to'];
    $output4[$key] = "from $from to $to";
    $total4 += abs($to - $from);
}
$time5 = microtime(true);
$output5 = setup();
$time6 = microtime(true);

semaphore_release('setup');
output_handler_json([
    'system' => [
        'time' => round($time1 - $time0, 6),
        'output' => $output0,
        'count' => count($output0),
    ],
    'directories' => [
        'time' => round($time2 - $time1, 6),
        'output' => $output1,
        'count' => count($output1),
    ],
    'composer' => [
        'time' => round($time3 - $time2, 6),
        'output' => $output2,
        'count' => count($output2),
    ],
    'db_schema' => [
        'time' => round($time4 - $time3, 6),
        'check' => $dbschema_check,
        'hash' => $dbschema_hash,
        'output' => $output3,
        'count' => count($output3),
    ],
    'db_static' => [
        'time' => round($time5 - $time4, 6),
        'check' => $dbstatic_check,
        'hash' => $dbstatic_hash,
        'output' => $output4,
        'count' => $total4,
    ],
    'setup' => [
        'time' => round($time6 - $time5, 6),
        'output' => $output5,
        'count' => array_sum($output5),
    ],
]);
