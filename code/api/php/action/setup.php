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
require_once __ROOT__ . 'php/lib/system.php';
require_once __ROOT__ . 'php/lib/dbschema.php';
require_once __ROOT__ . 'php/lib/setup.php';

$dbschema_check = __dbschema_check();
$dbschema_hash = __dbschema_hash();
$dbstatic_check = __dbstatic_check();
$dbstatic_hash = __dbstatic_hash();

$time0 = microtime(true);
$output0 = check_system();
foreach ($output0 as $key => $val) {
    if (isset($val['error'])) {
        show_php_error([
            'phperror' => $val['error'],
            'details' => $val['details'],
        ]);
    }
}
$time1 = microtime(true);
$output1 = db_schema();
$time2 = microtime(true);
$output2 = db_static();
$total2 = 0;
foreach ($output2 as $key => $val) {
    $from = $val['from'];
    $to = $val['to'];
    $output2[$key] = "from $from to $to";
    $total2 += abs($to - $from);
}
$time3 = microtime(true);
$output3 = setup();
$time4 = microtime(true);

semaphore_release('setup');
output_handler_json([
    'system' => [
        'time' => round($time1 - $time0, 6),
        'output' => $output0,
        'count' => count($output0),
    ],
    'db_schema' => [
        'time' => round($time2 - $time1, 6),
        'check' => $dbschema_check,
        'hash' => $dbschema_hash,
        'history' => $output1,
        'count' => count($output1),
    ],
    'db_static' => [
        'time' => round($time3 - $time2, 6),
        'check' => $dbstatic_check,
        'hash' => $dbstatic_hash,
        'history' => $output2,
        'count' => $total2,
    ],
    'setup' => [
        'time' => round($time4 - $time3, 6),
        'history' => $output3,
        'count' => array_sum($output3),
    ],
]);
