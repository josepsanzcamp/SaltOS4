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
 * Add log action
 *
 * This file implements the addlog action, requires a POST JSON request
 * with an element in the json that contains the message to be added
 *
 * @msg => message that you want to add to the log file
 *
 * Add error action
 *
 * This file implements the adderror action, requires a POST JSON request
 * with the follow elements: jserror, details and backtrace, this action
 * is called from window.onerror in order to store the details of the js
 * error
 *
 * @jserror   => text used as title in the error report
 * @details   => text used as details in the error report
 * @backtrace => array with the backtrace used in the error report
 */

$action = get_data('rest/1');
switch ($action) {
    case 'log':
        if (get_data('json/msg') === null) {
            show_json_error('msg not found');
        }
        addlog(get_data('json/msg'));
        break;
    case 'error':
        foreach (['jserror', 'details', 'backtrace'] as $key) {
            if (get_data("json/$key") === null) {
                show_json_error("$key not found");
            }
        }
        addtrace([
            'jserror' => get_data('json/jserror'),
            'details' => get_data('json/details'),
            'backtrace' => get_data('json/backtrace'),
        ], get_config('debug/jserrorfile') ?? 'jserror.log');
        break;
    default:
        show_php_error(['phperror' => "Unknown action $action"]);
}

output_handler_json([
    'status' => 'ok',
]);
