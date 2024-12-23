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
 * Authentication helper module
 *
 * This file contains all needed code to do authentications with all features suck as the
 * main authentication using a user and password pair, the checktoken and the deauthtoken
 * to control it.
 */

if (!semaphore_acquire('auth')) {
    show_php_error(['phperror' => 'Could not acquire the semaphore']);
}

db_connect();
crontab_users();

require_once 'php/lib/auth.php';
$array = [];
$action = get_data('rest/1');
switch ($action) {
    case 'login':
        // Check parameters
        foreach (['user', 'pass'] as $key) {
            if (get_data("json/$key") === null) {
                semaphore_release('auth');
                show_json_error("$key not found");
            }
        }
        $array = authtoken(get_data('json/user'), get_data('json/pass'));
        break;
    case 'logout':
        $array = deauthtoken();
        break;
    case 'check':
        $array = checktoken();
        break;
    case 'update':
        // Check parameters
        foreach (['oldpass', 'newpass', 'renewpass'] as $key) {
            if (get_data("json/$key") === null) {
                semaphore_release('auth');
                show_json_error("$key not found");
            }
        }
        $array = authupdate(get_data('json/oldpass'), get_data('json/newpass'), get_data('json/renewpass'));
        break;
    default:
        show_php_error(['phperror' => "Unknown action $action"]);
}

semaphore_release('auth');
output_handler_json($array);
