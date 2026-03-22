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

switch (get_data('rest/1')) {
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
        show_php_error(['phperror' => 'Unknown action']);
}

output_handler_json([
    'status' => 'ok',
]);
