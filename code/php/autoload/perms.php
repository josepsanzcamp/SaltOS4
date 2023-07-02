<?php

/**
 ____        _ _    ___  ____    _  _    ___
/ ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
\___ \ / _` | | __| | | \___ \  | || |_| | | |
 ___) | (_| | | |_| |_| |___) | |__   _| |_| |
|____/ \__,_|_|\__|\___/|____/     |_|(_)___/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2023 by Josep Sanz Campderrós
More information in https://www.saltos.org or info@saltos.org

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

declare(strict_types=1);

/**
 * Check Perms
 *
 * This function checks the permissions using the tables apps_perms,
 * users_apps_perms and groups_apps_perms, to do it, this function uses
 * the user_id and groups_id (note that groups_id contains all groups
 * where the user is associated), and try to check that the permissions
 * permutations exists in the apps_perms, if some permission is found
 * in the users and groups tables and it is not found in the apps_perms,
 * an integrity error is launched.
 *
 * @app => the app to check
 * @perm => the perm to check
 */
function check_perms($app, $perm)
{
    if (!app_exists($app) || !perm_exists($perm)) {
        return false;
    }
    // Get all permissions with all permutations
    $query = "SELECT app_id, perm_id, allow, deny FROM tbl_apps_perms";
    $from_apps_perms = execute_query_array($query);
    // Get all relevant permissions associated to the user
    $user_id = current_user();
    $query = "SELECT app_id, perm_id, allow, deny FROM tbl_users_apps_perms WHERE user_id = $user_id";
    $from_users_apps_perms = execute_query_array($query);
    // Get all relevant permissions associated to the groups associated to the user
    $groups_id = current_groups();
    $query = "SELECT app_id, perm_id, allow, deny FROM tbl_groups_apps_perms WHERE group_id IN ($groups_id)";
    $from_groups_apps_perms = execute_query_array($query);
    // Compute the resulting array with all permissions
    $array = array();
    foreach ($from_apps_perms as $row) {
        $key = $row["app_id"] . "|" . $row["perm_id"];
        $array[$key] = $row;
        $array[$key]["app"] = id2app($row["app_id"]);
        $array[$key]["perm"] = id2perm($row["perm_id"]);
    }
    foreach (array_merge($from_users_apps_perms, $from_groups_apps_perms) as $row) {
        $key = $row["app_id"] . "|" . $row["perm_id"];
        if (!isset($array[$key])) {
            show_php_error(array("phperror" => "Integrity error for $key in " . __FUNCTION__));
        }
        $array[$key]["allow"] += $row["allow"];
        $array[$key]["deny"] += $row["deny"];
    }
    // Apply the filter
    foreach ($array as $key => $val) {
        if ($val["deny"] || !$val["allow"]) {
            unset($array[$key]);
        }
    }
    // Return the result if exists
    $key = app2id($app) . "|" . perm2id($perm);
    return isset($array[$key]);
}

/**
 * TODO
 */
function check_sql($app, $perms)
{
    // TODO
}

/**
 * Perms helper function
 *
 * This function is used by the XXX2YYY functions as helper, it stores the
 * dictionary of all conversions and resolves the data using it
 *
 * @fn => the caller function
 * @arg => the argument passed to the function
 */
function __perms($fn, $arg)
{
    static $dict = array();
    if (!count($dict)) {
        $query = "SELECT * FROM tbl_perms WHERE active = 1";
        $result = db_query($query);
        $dict["id2perm"] = array();
        $dict["perm2id"] = array();
        while ($row = db_fetch_row($result)) {
            $dict["id2perm"][$row["id"]] = $row["code"];
            $dict["perm2id"][$row["code"]] = $row["id"];
        }
        db_free($result);
    }
    if ($fn == "perm_exists") {
        return isset($dict["perm2id"][$arg]);
    }
    if (!isset($dict[$fn][$arg])) {
        show_php_error(array("phperror" => "$fn($arg) not found"));
    }
    return $dict[$fn][$arg];
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
 */
function perm_exists($perm)
{
    return __perms(__FUNCTION__, $perm);
}
