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
 * Authentication helper module
 *
 * This file contains all needed code to do authentications with all features suck as the
 * main authentication using a user and password pair, the checktoken and the deauthtoken
 * to control it.
 */

// fast echo test
if (get_data('rest/1') === 'test') {
    output_handler_json(['token' => get_data('server/token')]);
}

// normal operation
if (!semaphore_acquire('auth')) {
    show_php_error(['phperror' => 'Could not acquire the semaphore']);
}

db_connect();
crontab_users();

require_once 'php/lib/auth.php';
$array = [];
switch (get_data('rest/1')) {
    case 'login':
        $array = authtoken(get_data('json/user'), get_data('json/pass'));
        break;
    case 'logout':
        $array = deauthtoken();
        break;
    case 'check':
        $array = checktoken();
        break;
    case 'update':
        $array = authupdate(get_data('json/oldpass'), get_data('json/newpass'), get_data('json/renewpass'));
        break;
    default:
        show_php_error(['phperror' => 'Unknown action']);
}

semaphore_release('auth');
output_handler_json($array);
