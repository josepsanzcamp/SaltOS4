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

db_connect();
require_once 'php/lib/push.php';

$rows = [];
switch (get_data('rest/1')) {
    case 'get':
        if (!current_user()) {
            show_json_error('Permission denied', true);
        }
        $timestamp = floatval(get_data('rest/2'));
        if (!$timestamp) {
            show_php_error(['phperror' => "Unknown timestamp $timestamp"]);
        }
        $rows = push_select($timestamp);
        break;
    case 'set':
        if (!get_data('server/xuid') || !current_user()) {
            show_php_error(['phperror' => 'Permission denied']);
        }
        $timestamp = microtime(true) - 1e-3;
        push_insert(get_data('rest/2'), get_data('rest/3'));
        $rows = push_select($timestamp);
        break;
    default:
        show_php_error(['phperror' => 'Unknown action']);
}

output_handler_json($rows);
