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

db_connect();
$user_id = current_user();
if (!$user_id) {
    show_json_error('Permission denied');
}

require_once 'php/lib/push.php';

if (get_data('rest/1') == 'success') {
    $timestamp = microtime(true) - 1e-3;
    push_insert('success', 'This is a success test message');
    output_handler_json(push_select($timestamp));
}

if (get_data('rest/1') == 'danger') {
    $timestamp = microtime(true) - 1e-3;
    push_insert('danger', 'This is a danger test message');
    output_handler_json(push_select($timestamp));
}

if (get_data('rest/1') == 'email') {
    $timestamp = microtime(true) - 1e-3;
    push_insert('event', 'saltos.emails.update');
    output_handler_json(push_select($timestamp));
}

$timestamp = floatval(get_data('rest/1'));
if (!$timestamp) {
    $timestamp = microtime(true);
}
$rows = push_select($timestamp);
output_handler_json($rows);
