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
 * Permissions helper module
 *
 * This fie contains useful functions related to permissions, allow to apply permissions in php core
 * or in sql queries, to do it, uses all permissions tables and predefined configurations, more info
 * in each function
 */

/**
 * Check User
 *
 * This function checks the permissions using the tables apps_perms,
 * users_apps_perms and groups_apps_perms, to do it, this function uses
 * the user_id and groups_id (note that groups_id contains all groups
 * where the user is associated), and try to check that the permissions
 * permutations exists in the apps_perms, if some permission is found
 * in the users and groups tables and it is not found in the apps_perms,
 * an integrity error is launched.
 *
 * @app  => the app to check
 * @perm => the perm to check
 */
function check_user($app, $perm)
{
    static $array = null;
    static $user_id = null;
    static $groups_id = null;
    if ($array === null || $user_id != current_user() || $groups_id != current_groups()) {
        // Get all permissions with all permutations
        $query = 'SELECT app_id, perm_id, allow, deny FROM tbl_apps_perms';
        $from_apps_perms = execute_query_array($query);
        // Get all relevant permissions associated to the user
        $user_id = current_user();
        $query = "SELECT app_id, perm_id, allow, deny
            FROM tbl_users_apps_perms WHERE user_id = $user_id";
        $from_users_apps_perms = execute_query_array($query);
        // Get all relevant permissions associated to the groups associated to the user
        $groups_id = current_groups();
        $query = "SELECT app_id, perm_id, allow, deny
            FROM tbl_groups_apps_perms WHERE group_id IN ($groups_id)";
        $from_groups_apps_perms = execute_query_array($query);
        // Compute the resulting array with all permissions
        $array = [];
        foreach ($from_apps_perms as $row) {
            $key = $row['app_id'] . '|' . $row['perm_id'];
            $array[$key] = $row;
            $array[$key]['app'] = id2app($row['app_id']);
            $array[$key]['perm'] = id2perm($row['perm_id']);
        }
        foreach (array_merge($from_users_apps_perms, $from_groups_apps_perms) as $row) {
            $key = $row['app_id'] . '|' . $row['perm_id'];
            if (!isset($array[$key])) {
                show_php_error(['phperror' => "Internal error for $key"]);
            }
            $array[$key]['allow'] += $row['allow'];
            $array[$key]['deny'] += $row['deny'];
        }
        // Apply the filter
        foreach ($array as $key => $val) {
            if ($val['deny'] || !$val['allow']) {
                unset($array[$key]);
            }
        }
    }
    /* Important Note: The follow check exists because when someone try to
     * validate some app by some perm, the main idea is that the app and the
     * perm exists, other thing is that the user not has permission, in this
     * case the last isset returns false and nothing to do */
    if (!app_exists($app)) {
        show_php_error(['phperror' => "App $app not found"]);
    }
    if (!perm_exists($perm)) {
        show_php_error(['phperror' => "Perm $perm not found"]);
    }
    // Special case when ask for perm with owners
    $app_id = app2id($app);
    $perm_id = perm2id($perm);
    if (is_array($perm_id)) {
        foreach ($perm_id as $temp) {
            $key = $app_id . '|' . $temp;
            if (isset($array[$key])) {
                return true;
            }
        }
        return false;
    }
    // Return the result if exists
    $key = $app_id . '|' . $perm_id;
    return isset($array[$key]);
}

/**
 * Check SQL
 *
 * This function returns the fragment of SQL intended to filter by app and
 * perm for the current user
 *
 * @app  => the app to check
 * @perm => the perm to check
 *
 * Notes:
 *
 * This function returns the portion of sql used to check permissions
 * associated to an user with a specific permission and to an specific
 * register, as an optimization, it detects if the all owner is on and
 * return a true expression to improve the performance
 */
function check_sql($app, $perm)
{
    $table = app2table($app);
    $user_id = current_user();
    $groups_id = current_groups();
    /* This temporary variable allocate the sequence of FIND_IN_SET used to do
     * the intersection between the groups_id of the current user and the
     * groups_id of the control table, imagine that you have a user that is
     * associated to the groups 1,2,3 and you must to do a FIND_IN_SET of each
     * group (1,2,3) with the contents of the groups_id of the control table,
     * this is not possible and to solve this issue, we must to prepare the
     * $temp variable with the list of FIND_IN_SET of each group with the
     * field that can contains another list of ids, in other words, this trick
     * tries to solve the FIND_IN_SET('1,2,3', '2,3,4') */
    $temp = explode(',', $groups_id);
    foreach ($temp as $key => $val) {
        $temp[$key] = "FIND_IN_SET($val,groups_id)";
    }
    $temp = implode(' OR ', $temp);
    // Continue
    $sql = [
        'all' => '1=1',
        'group' => "id IN (SELECT id FROM {$table}_control
            WHERE group_id IN ($groups_id) OR $temp)",
        'user' => "id IN (SELECT id FROM {$table}_control
            WHERE user_id IN ($user_id) OR FIND_IN_SET($user_id,users_id))",
    ];
    foreach ($sql as $key => $val) {
        if (!check_user($app, $perm . '|' . $key)) {
            unset($sql[$key]);
        }
    }
    if (!count($sql)) {
        return '1=0';
    }
    if (isset($sql['all'])) {
        return '1=1';
    }
    return '(' . implode(' OR ', $sql) . ')';
}

/**
 * Perms helper function
 *
 * This function is used by the XXX2YYY functions as helper, it stores the
 * dictionary of all conversions and resolves the data using it
 *
 * @fn  => the caller function
 * @arg => the argument passed to the function
 */
function __perms($fn, $arg)
{
    static $dict = [];
    if (!count($dict)) {
        $query = 'SELECT * FROM tbl_perms WHERE active = 1';
        $result = db_query($query);
        $dict['id2perm'] = [];
        $dict['perm2id'] = [];
        while ($row = db_fetch_row($result)) {
            if ($row['owner'] != '') {
                if (!isset($dict['perm2id'][$row['code']])) {
                    $dict['perm2id'][$row['code']] = [];
                }
                $dict['perm2id'][$row['code']][] = $row['id'];
                $row['code'] .= '|' . $row['owner'];
            }
            $dict['id2perm'][$row['id']] = $row['code'];
            $dict['perm2id'][$row['code']] = $row['id'];
        }
        db_free($result);
    }
    if ($fn == 'perm_exists') {
        return isset($dict['perm2id'][$arg]);
    }
    if (isset($dict[$fn][$arg])) {
        return $dict[$fn][$arg];
    }
    show_php_error(['phperror' => "$fn($arg) not found"]);
}

/**
 * Id to Perm
 *
 * This function resolves the code of the perm from the perm id
 *
 * @id => the id used to resolve the perm
 */
function id2perm($id)
{
    return __perms(__FUNCTION__, $id);
}

/**
 * Perm to Id
 *
 * This function resolves the id of the perm from the perm code
 *
 * @perm => the perm code used to resolve the id
 *
 * Notes:
 *
 * This function can return an integer or an array of integers, depending
 * if the app is using the owner parameter or not
 */
function perm2id($perm)
{
    return __perms(__FUNCTION__, $perm);
}

/**
 * Perm Exists
 *
 * This function detect if a perm exists
 *
 * @perm => the perm that you want to check if exists
 *
 * Notes:
 *
 * This function returns true if a perm exists, and in case of the usage
 * of the owner parameter, the function will return true for a perm that
 * contains the owner and for the perm without the owner, for exampe, this
 * function returns true for perm list and form perm list|user
 */
function perm_exists($perm)
{
    return __perms(__FUNCTION__, $perm);
}

/**
 * Check App Perm Id
 *
 * This function returns true if the app, the perm and the id accomplishes the
 * expected level of permissions, it is intended to be used before the execution
 * of each action, to guarantee the security
 *
 * @app  => the app to check
 * @perm => the perm to check
 * @id   => the id to check, if needed, you can omit in the create case
 */
function check_app_perm_id($app, $perm, $id = null)
{
    if (!check_user($app, $perm)) {
        return false;
    }
    if ($id === null) {
        return true;
    }
    $table = app2table($app);
    $sql = check_sql($app, $perm);
    $id = intval($id);
    $query = "SELECT id FROM $table WHERE id = $id AND $sql";
    $exists = execute_query($query);
    if (!$exists) {
        return false;
    }
    return true;
}

/**
 * User is admin
 *
 * This function returns true if the current user has all perms for the app
 */
function __user_is_admin($app)
{
    $app_id = app2id($app);
    $query = "SELECT * FROM tbl_perms
        WHERE active = 1 AND id IN (SELECT perm_id FROM tbl_apps_perms WHERE app_id=$app_id)";
    $rows = execute_query_array($query);
    foreach ($rows as $row) {
        if ($row['owner'] != '') {
            $perm = $row['code'] . '|' . $row['owner'];
        } else {
            $perm = $row['code'];
        }
        if (!check_user($app, $perm)) {
            return false;
        }
    }
    return true;
}
