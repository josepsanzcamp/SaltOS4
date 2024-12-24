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
 * Setup function
 *
 * This function allow to create the minimal user and group information to do SaltOS
 * usable by the admin user, only do things if the keys tables of the array are void,
 * too is able to maintain the integrity of the tbl_{users,groups}_apps_perms tables
 * by removing the unused registers, usefull when you modify the tbl_apps_perms rows
 */
function setup()
{
    $output = [];

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
            FROM tbl_apps_perms WHERE allow = 0 AND deny = 0"),
        'tbl_groups_apps_perms' => execute_query_array("
            SELECT id, '1' group_id, app_id, perm_id, '1' allow, '0' deny
            FROM tbl_apps_perms WHERE allow = 0 AND deny = 0"),
    ];

    foreach ($array as $table => $rows) {
        $output[$table] = 0;
        $exists = execute_query("SELECT COUNT(*) FROM $table");
        if ($exists) {
            continue;
        }
        foreach ($rows as $row) {
            $query = prepare_insert_query($table, $row);
            db_query(...$query);
            $output[$table]++;
        }
    }

    require_once 'php/lib/control.php';
    require_once 'php/lib/log.php';
    require_once 'php/lib/version.php';
    require_once 'php/lib/indexing.php';

    if ($output['tbl_users']) {
        make_control('users', 1);
        make_log('users', 1, 'setup');
        make_version('users', 1);
        make_index('users', 1);
    }

    if ($output['tbl_groups']) {
        make_control('groups', 1);
        make_log('groups', 1, 'setup');
        make_version('groups', 1);
        make_index('groups', 1);
    }

    $array = [
        'tbl_users_apps_perms' => execute_query_array("SELECT * FROM tbl_users_apps_perms
            WHERE CONCAT(app_id,'|',perm_id) NOT IN (SELECT CONCAT(app_id,'|',perm_id) FROM tbl_apps_perms)"),
        'tbl_groups_apps_perms' => execute_query_array("SELECT * FROM tbl_groups_apps_perms
            WHERE CONCAT(app_id,'|',perm_id) NOT IN (SELECT CONCAT(app_id,'|',perm_id) FROM tbl_apps_perms)"),
    ];

    foreach ($array as $table => $rows) {
        foreach ($rows as $row) {
            $query = "DELETE FROM $table WHERE id = ?";
            db_query($query, [$row['id']]);
            $output[$table]--;
        }
    }

    return $output;
}
