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
 * Setup helper module
 *
 * This file contains useful functions related to the setup process
 */

if (get_data('server/request_method') != 'CLI') {
    show_php_error(['phperror' => 'Permission denied']);
}

require_once 'php/lib/control.php';
require_once 'php/lib/indexing.php';
$time1 = microtime(true);
$output = [
    'total' => 0,
];

// Import invoices
$exists = execute_query('SELECT COUNT(*) FROM app_invoices');
if (!$exists) {
    $files = glob('apps/invoices/sample/*.sql.gz');
    foreach ($files as $file) {
        $query = file_get_contents('compress.zlib://' . $file);
        db_query($query);
    }
    // Insert the control register
    $ids = execute_query_array('SELECT id FROM app_invoices');
    foreach ($ids as $id) {
        make_control('invoices', $id);
        make_version('invoices', $id);
        make_index('invoices', $id);
        $output['total']++;
    }
}

$time2 = microtime(true);
output_handler_json([
    'setup' => array_merge([
        'time' => round($time2 - $time1, 6),
    ], $output),
]);
