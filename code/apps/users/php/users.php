<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2025 by Josep Sanz Campderrós
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
 * Users functions
 *
 * This file contain all functions needed by the users app
 */

/**
 * Insert user action
 *
 * This action allow to insert registers in the database associated to
 * the users app and only requires the data.
 *
 * @data => the array with all data used to create the new user
 */
function insert_user($data)
{
    require_once 'php/lib/actions.php';
    require_once 'php/lib/auth.php';
    require_once 'php/lib/version.php';

    if (!is_array($data) || !count($data)) {
        return [
            'status' => 'ko',
            'text' => 'Data not found',
            'code' => __get_code_from_trace(),
        ];
    }

    $newpass = $data['newpass'] ?? null;
    $renewpass = $data['renewpass'] ?? null;
    $perms = $data['perms'] ?? null;
    unset($data['newpass']);
    unset($data['renewpass']);
    unset($data['perms']);

    if (!$newpass || !$renewpass) {
        return [
            'status' => 'ko',
            'text' => 'Do you must enter the new passwords',
            'code' => __get_code_from_trace(),
        ];
    }

    // Password checks
    if ($newpass != $renewpass) {
        return [
            'status' => 'ko',
            'text' => 'New password differs',
            'code' => __get_code_from_trace(),
        ];
    }

    if (!score_check($newpass)) {
        return [
            'status' => 'ko',
            'text' => 'New password strength error',
            'code' => __get_code_from_trace(),
        ];
    }

    // Real insert using general insert action
    $array = insert('users', $data);
    if ($array['status'] == 'ko') {
        return $array;
    }
    $user_id = $array['created_id'];

    // Continue creating the password entry
    newpass_insert($user_id, $newpass);

    // Create the perms entries
    if (is_array($perms)) {
        foreach ($perms as $perm) {
            $query = prepare_insert_query('tbl_users_apps_perms', [
                'user_id' => $user_id,
                'app_id' => $perm['app_id'],
                'perm_id' => $perm['perm_id'],
                'allow' => $perm['allow'],
                'deny' => $perm['deny'],
            ]);
            db_query(...$query);
        }
    }

    // note: the next del_version is because this function add
    // more data and it is executed at the end of the function
    del_version('users', $user_id);
    make_version('users', $user_id);

    return [
        'status' => 'ok',
        'created_id' => $user_id,
    ];
}

/**
 * Update user action
 *
 * This action allow to update registers in the database associated to
 * the users app and requires the user_id and data.
 *
 * @user_id => the user_id desired to be updated
 * @data    => the array with all data used to update the user
 */
function update_user($user_id, $data)
{
    require_once 'php/lib/actions.php';
    require_once 'php/lib/auth.php';
    require_once 'php/lib/log.php';
    require_once 'php/lib/version.php';

    if (!is_array($data) || !count($data)) {
        return [
            'status' => 'ko',
            'text' => 'Data not found',
            'code' => __get_code_from_trace(),
        ];
    }

    $newpass = $data['newpass'] ?? null;
    $renewpass = $data['renewpass'] ?? null;
    $perms = $data['perms'] ?? null;
    unset($data['newpass']);
    unset($data['renewpass']);
    unset($data['perms']);

    if ($newpass || $renewpass) {
        // Password checks
        if ($newpass != $renewpass) {
            return [
                'status' => 'ko',
                'text' => 'New password differs',
                'code' => __get_code_from_trace(),
            ];
        }

        if (!score_check($newpass)) {
            return [
                'status' => 'ko',
                'text' => 'New password strength error',
                'code' => __get_code_from_trace(),
            ];
        }

        if (!newpass_check($user_id, $newpass)) {
            return [
                'status' => 'ko',
                'text' => 'New password used previously',
                'code' => __get_code_from_trace(),
            ];
        }
    }

    // Real update using general update action
    if (count($data)) {
        $array = update('users', $user_id, $data);
        if ($array['status'] == 'ko') {
            return $array;
        }
    }

    // Continue creating the password entry
    if ($newpass || $renewpass) {
        oldpass_disable($user_id);
        newpass_insert($user_id, $newpass);
    }

    if (is_array($perms)) {
        $query = 'SELECT * FROM tbl_users_apps_perms WHERE user_id = ?';
        $old_perms = execute_query_array($query, [$user_id]);
        foreach ($perms as $key => $val) {
            foreach ($old_perms as $old_key => $old_val) {
                if (
                    $val['app_id'] == $old_val['app_id'] &&
                    $val['perm_id'] == $old_val['perm_id'] &&
                    $val['allow'] == $old_val['allow'] &&
                    $val['deny'] == $old_val['deny']
                ) {
                    unset($perms[$key]);
                    unset($old_perms[$old_key]);
                }
            }
        }

        // Delete the old perms entries
        foreach ($old_perms as $perm) {
            $query = 'DELETE FROM tbl_users_apps_perms WHERE user_id = ? AND id = ?';
            db_query($query, [$user_id, $perm['id']]);
        }

        // Create the perms entries
        foreach ($perms as $perm) {
            $query = prepare_insert_query('tbl_users_apps_perms', [
                'user_id' => $user_id,
                'app_id' => $perm['app_id'],
                'perm_id' => $perm['perm_id'],
                'allow' => $perm['allow'],
                'deny' => $perm['deny'],
            ]);
            db_query(...$query);
        }
    }

    if (count($data)) {
        // note: the next del_version is because this function add
        // more data and it is executed at the end of the function
        del_version('users', $user_id);
        make_version('users', $user_id);
    } else {
        make_log('users', 'update', $user_id);
        make_version('users', $user_id);
    }

    return [
        'status' => 'ok',
        'updated_id' => $user_id,
    ];
}

/**
 * Delete user action
 *
 * This action allow to delete registers in the database associated to
 * the users app and only requires the user_id.
 *
 * @group_id => the user_id desired to be deleted
 */
function delete_user($user_id)
{
    require_once 'php/lib/actions.php';
    require_once 'php/lib/version.php';

    // Real delete using general delete action
    $array = delete('users', $user_id);
    if ($array['status'] == 'ko') {
        return $array;
    }

    // Continue removing the passwords entries
    $query = 'DELETE FROM tbl_users_passwords WHERE user_id = ?';
    db_query($query, [$user_id]);

    // Continue removing the perms entries
    $query = 'DELETE FROM tbl_users_apps_perms WHERE user_id = ?';
    db_query($query, [$user_id]);

    del_version('users', $user_id);
    make_version('users', $user_id);

    return [
        'status' => 'ok',
        'deleted_id' => $user_id,
    ];
}
