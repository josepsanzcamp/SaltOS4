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
 * Groups functions
 *
 * This file contain all functions needed by the groups app
 */

/**
 * Insert group action
 *
 * This action allow to insert registers in the database associated to
 * the groups app and only requires the data.
 *
 * @data => the array with all data used to create the new group
 */
function insert_group($data)
{
    require_once 'php/lib/actions.php';
    require_once 'php/lib/version.php';

    if (!is_array($data) || !count($data)) {
        return [
            'status' => 'ko',
            'text' => 'Data not found',
            'code' => __get_code_from_trace(),
        ];
    }

    $perms = $data['perms'] ?? null;
    unset($data['perms']);

    // Real insert using general insert action
    $array = insert('groups', $data);
    if ($array['status'] == 'ko') {
        return $array;
    }
    $group_id = $array['created_id'];

    // Create the perms entries
    if (is_array($perms)) {
        foreach ($perms as $perm) {
            $query = prepare_insert_query('tbl_groups_apps_perms', [
                'group_id' => $group_id,
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
    del_version('groups', $group_id);
    make_version('groups', $group_id);

    return [
        'status' => 'ok',
        'created_id' => $group_id,
    ];
}

/**
 * Update group action
 *
 * This action allow to update registers in the database associated to
 * the groups app and requires the group_id and data.
 *
 * @group_id => the group_id desired to be updated
 * @data     => the array with all data used to update the group
 */
function update_group($group_id, $data)
{
    require_once 'php/lib/actions.php';
    require_once 'php/lib/log.php';
    require_once 'php/lib/version.php';

    if (!is_array($data) || !count($data)) {
        return [
            'status' => 'ko',
            'text' => 'Data not found',
            'code' => __get_code_from_trace(),
        ];
    }

    $perms = $data['perms'] ?? null;
    unset($data['perms']);

    // Real update using general update action
    if (count($data)) {
        $array = update('groups', $group_id, $data);
        if ($array['status'] == 'ko') {
            return $array;
        }
    }

    if (is_array($perms)) {
        $query = 'SELECT * FROM tbl_groups_apps_perms WHERE group_id = ?';
        $old_perms = execute_query_array($query, [$group_id]);
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
            $query = 'DELETE FROM tbl_groups_apps_perms WHERE group_id = ? AND id = ?';
            db_query($query, [$group_id, $perm['id']]);
        }

        // Create the perms entries
        foreach ($perms as $perm) {
            $query = prepare_insert_query('tbl_groups_apps_perms', [
                'group_id' => $group_id,
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
        del_version('groups', $group_id);
        make_version('groups', $group_id);
    } else {
        make_log('groups', 'update', $group_id);
        make_version('groups', $group_id);
    }

    return [
        'status' => 'ok',
        'updated_id' => $group_id,
    ];
}

/**
 * Delete group action
 *
 * This action allow to delete registers in the database associated to
 * the groups app and only requires the group_id.
 *
 * @group_id => the group_id desired to be deleted
 */
function delete_group($group_id)
{
    require_once 'php/lib/actions.php';
    require_once 'php/lib/version.php';

    // Real delete using general delete action
    $array = delete('groups', $group_id);
    if ($array['status'] == 'ko') {
        return $array;
    }

    // Continue removing the perms entries
    $query = 'DELETE FROM tbl_groups_apps_perms WHERE group_id = ?';
    db_query($query, [$group_id]);

    del_version('groups', $group_id);
    make_version('groups', $group_id);

    return [
        'status' => 'ok',
        'deleted_id' => $group_id,
    ];
}
