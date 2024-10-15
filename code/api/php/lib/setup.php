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

/**
 * TODO
 *
 * TODO
 */
function setup()
{
    $output = [
        'history' => [],
        'count' => 0,
    ];

    $token = get_unique_token();
    set_data('server/token', $token);

    $array = [
        'tbl_users' => [
            [
                'id' => 1,
                'active' => 1,
                'group_id' => 1,
                'login' => 'admin',
                'name' => 'Admin',
                'description' => 'Admin user',
                'start' => '00:00:00',
                'end' => '23:59:59',
                'days' => '1111111',
            ],
        ],
        'tbl_users_passwords' => [
            [
                'id' => 1,
                'active' => 1,
                'user_id' => 1,
                'created_at' => current_datetime(),
                'remote_addr' => get_data('server/remote_addr'),
                'user_agent' => get_data('server/user_agent'),
                'password' => password_hash('admin', PASSWORD_DEFAULT),
                'expires_at' => current_datetime(get_config('auth/passwordexpires')),
            ],
        ],
        'tbl_groups' => [
            [
                'id' => 1,
                'active' => 1,
                'code' => 'admin',
                'name' => 'Admin',
                'description' => 'Admin group',
            ],
        ],
        'tbl_users_apps_perms' => execute_query_array("
            SELECT id, '1' user_id, app_id, perm_id, '1' allow, '0' deny
            FROM tbl_apps_perms"),
        'tbl_groups_apps_perms' => execute_query_array("
            SELECT id, '1' group_id, app_id, perm_id, '1' allow, '0' deny
            FROM tbl_apps_perms"),
        'tbl_users_tokens' => [
            [
                'id' => 1,
                'active' => 1,
                'user_id' => 1,
                'created_at' => current_datetime(),
                'remote_addr' => get_data('server/remote_addr'),
                'user_agent' => get_data('server/user_agent'),
                'token' => get_data('server/token'),
                'expires_at' => current_datetime(get_config('auth/tokenshortexpires')),
            ],
        ],
    ];

    foreach ($array as $table => $rows) {
        $output['history'][$table] = 0;
        $exists = execute_query("SELECT COUNT(*) FROM $table");
        if ($exists) {
            continue;
        }
        foreach ($rows as $row) {
            $query = prepare_insert_query($table, $row);
            db_query(...$query);
            $output['history'][$table]++;
            $output['count']++;
        }
    }

    require_once 'php/lib/control.php';
    require_once 'php/lib/indexing.php';

    if (isset($output['history']['tbl_users'])) {
        make_control('users', 1);
        make_version('users', 1);
        make_index('users', 1);
    }

    if (isset($output['history']['tbl_groups'])) {
        make_control('groups', 1);
        make_version('groups', 1);
        make_index('groups', 1);
    }

    return $output;
}
