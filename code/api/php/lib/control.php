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
 * Control helper module
 *
 * This fie contains useful functions related to the control and version system, they allow to
 * relationate registers with users and groups, and to add and retrieve the versions of a register
 */

/**
 * Make Control function
 *
 * This function allow to insert and delete the control registers associacted
 * to any application and to any register of the application
 *
 * @app      => code of the application that you want to index
 * @reg_id   => register of the app that you want to index
 * @user_id  => user id of the owner of the app register
 * @datetime => time mark used as creation time of the app register
 *
 * Notes:
 *
 * You can pass a null user_id and/or null datetime, in these cases, the
 * function will determine the user_id and datetime automatically
 *
 * This function returns an integer as response about the control action:
 *
 * +1 => insert executed, this is because the app register exists and the control register not exists
 * +2 => delete executed, this is because the app register not exists and the control register exists
 * -1 => app not found, this is because the app requested not have a table in the apps config
 * -2 => control table not found, this is because the has_control feature is disabled by dbstatic
 * -3 => data not found, this is because the app register not exists and the control register too not exists
 * -4 => control exists, this is because the app register exists and the control register too exists
 *
 * As you can see, negative values denotes an error and positive values denotes a successfully situation
 */
function make_control($app, $reg_id, $user_id = null, $datetime = null)
{
    // Check the passed parameters
    $table = app2table($app);
    if ($table == "") {
        return -1;
    }
    if ($user_id === null) {
        $user_id = current_user();
    }
    if ($datetime === null) {
        $datetime = current_datetime();
    }
    // Check if control exists
    $query = "SELECT id FROM {$table}_control WHERE id='$reg_id'";
    if (!db_check($query)) {
        return -2;
    }
    $control_id = execute_query($query);
    // Check if exists data in the main table
    $query = "SELECT id FROM $table WHERE id='$reg_id'";
    $data_id = execute_query($query);
    if (!$data_id) {
        if (!$control_id) {
            return -3;
        } else {
            $query = "DELETE FROM {$table}_control WHERE id='$reg_id'";
            db_query($query);
            return 2;
        }
    }
    if (!$control_id) {
        $query = "SELECT group_id FROM tbl_users WHERE id=$user_id";
        $group_id = execute_query($query);
        $query = make_insert_query("{$table}_control", [
            "id" => $reg_id,
            "user_id" => $user_id,
            "group_id" => $group_id,
            "datetime" => $datetime,
        ]);
        db_query($query);
        return 1;
    } else {
        return -4;
    }
}

/**
 * Add Version function
 *
 * This function allow to add a new version to a reg_id of an app, to do it,
 * the function requires to specify the app, reg_id, the original data and
 * the new data to compute the diff patch that must to be stored in the data
 * field and create the register for the new version
 *
 * To do this, the function validate the input data, checks the existence
 * of registers of data and versions, prepare the data patch to store, get
 * the old hash to do the blockchain, get the last ver_id and compute all
 * needed things to do the insert of the new version register
 *
 * @app      => code of the application that you want to add a new version
 * @reg_id   => register of the app that you want to add a new version
 * @user_id  => user id of the owner of the version register
 * @datetime => time mark used as creation time of the version register
 *
 * Notes:
 *
 * You can pass a null user_id and/or null datetime, in these cases, the
 * function will determine the user_id and datetime automatically
 *
 * This function returns an integer as response about the control action:
 *
 * +1 => insert executed, this is because the app register exists and they can add a new version register
 * +2 => delete executed, this is because the app register not exists and the version register exists
 * -1 => app not found, this is because the app requested not have a table in the apps config
 * -2 => version table not found, this is because the has_version feature is disabled by dbstatic
 * -3 => data not found, this is because the app register not exists and the control register too not exists
 *
 * As you can see, negative values denotes an error and positive values denotes a successfully situation
 */
function add_version($app, $reg_id, $user_id = null, $datetime = null)
{
    // Check the passed parameters
    $table = app2table($app);
    if ($table == "") {
        return -1;
    }
    if ($user_id === null) {
        $user_id = current_user();
    }
    if ($datetime === null) {
        $datetime = current_datetime();
    }
    // Check if version exists
    $query = "SELECT MAX(id) FROM {$table}_version WHERE reg_id='$reg_id'";
    if (!db_check($query)) {
        return -2;
    }
    $version_id = execute_query($query);
    // Check if exists data in the main table
    $query = "SELECT id FROM $table WHERE id='$reg_id'";
    $data_id = execute_query($query);
    if (!$data_id) {
        if (!$version_id) {
            return -3;
        } else {
            $query = "DELETE FROM {$table}_version WHERE reg_id='$reg_id'";
            db_query($query);
            return 2;
        }
    }
    // Compute the diff from old data and new data
    $data_old = get_version($app, $reg_id, INF);
    $query = "SELECT * FROM $table WHERE id='$reg_id'";
    $data_new = [];
    $data_new[$table] = [];
    $data_new[$table][$reg_id] = execute_query($query);
    // Add the data from subtables, if exists
    $subtables = app2subtables($app);
    foreach ($subtables as $temp) {
        $subtable = $temp["subtable"];
        $field = $temp["field"];
        $query = "SELECT * FROM $subtable WHERE $field='$reg_id'";
        $data_new[$subtable] = [];
        $rows = execute_query_array($query);
        foreach ($rows as $key => $val) {
            $data_new[$subtable][$val["id"]] = $val;
        }
    }
    // Continue computing the diff from old data and new data
    $data = [];
    foreach ($data_new as $table => $rows) {
        $data[$table] = [];
        if (!isset($data_old[$table])) {
            $data_old[$table] = [];
        }
        foreach ($rows as $key => $val) {
            if (!isset($data_old[$table][$key])) {
                $data_old[$table][$key] = [];
            }
            $data[$table][$key] = array_diff_assoc($val, $data_old[$table][$key]);
        }
    }
    // Prepare extra data as old hash and last ver_id
    $table = app2table($app);
    $query = "SELECT hash FROM {$table}_version WHERE id='$version_id'";
    $hash_old = strval(execute_query($query));
    $query = "SELECT ver_id FROM {$table}_version WHERE id='$version_id'";
    $ver_id = intval(execute_query($query));
    // Prepare the array to the insert
    $array = [
        "user_id" => $user_id,
        "datetime" => $datetime,
        "reg_id" => $reg_id,
        "ver_id" => $ver_id + 1,
        "data" => base64_encode(serialize($data)),
        "hash" => $hash_old,
    ];
    // Update the hash with the new hash to do the blockchain
    $array["hash"] = md5(serialize($array));
    // Do the insert of the new version
    $query = make_insert_query("{$table}_version", $array);
    db_query($query);
    return 1;
}

/**
 * Get Version
 *
 * This function allow to get an specific version of a register and app, intended
 * to get the data used in a specific version to compare with other versions and
 * to restore data to the requested version
 *
 * @app    => code of the application that you want to add a new version
 * @reg_id => register of the app that you want to add a new version
 * @ver_id => the version that you want to get
 *
 * Notes:
 *
 * This function is not a simple select of the register that matches with the
 * ver_id requested, it does an accumulative merge to get the register data
 * in the moment where the version will be stored, to do it, they must to
 * restore versions from 1 to ver_id, and must to discard the next versions
 */
function get_version($app, $reg_id, $ver_id)
{
    $table = app2table($app);
    $query = "SELECT * FROM {$table}_version WHERE reg_id='$reg_id' ORDER BY id ASC";
    $rows = execute_query_array($query);
    $data = [];
    $hash_old = "";
    $datetime_old = "";
    $version_old = "";
    foreach ($rows as $row) {
        if ($row["ver_id"] > $ver_id) {
            break;
        }
        // Check the blockchain integrity
        $array = [
            "user_id" => $row["user_id"],
            "datetime" => $row["datetime"],
            "reg_id" => $row["reg_id"],
            "ver_id" => $row["ver_id"],
            "data" => $row["data"],
            "hash" => $hash_old,
        ];
        if ($row["hash"] != md5(serialize($array))) {
            $ver_id = $row["ver_id"];
            show_php_error(["phperror" => "Blockchain integrity breaked for $app:$reg_id:$ver_id"]);
        }
        // Check other vars that must accomplish that new values are greather or equal that old values
        if ($row["datetime"] < $datetime_old) {
            $ver_id = $row["ver_id"];
            show_php_error(["phperror" => "Blockchain integrity breaked for $app:$reg_id:$ver_id"]);
        }
        if ($row["ver_id"] < $version_old) {
            $ver_id = $row["ver_id"];
            show_php_error(["phperror" => "Blockchain integrity breaked for $app:$reg_id:$ver_id"]);
        }
        // Merge the new data with the data of the previous versions
        $data_new = unserialize(base64_decode($row["data"]));
        foreach ($data_new as $table => $temp) {
            if (!isset($data[$table])) {
                $data[$table] = [];
            }
            foreach ($temp as $key => $val) {
                if (!isset($data[$table][$key])) {
                    $data[$table][$key] = [];
                }
                $data[$table][$key] = array_merge($data[$table][$key], $val);
            }
            // This part is to emulate the delete command
            foreach ($data[$table] as $key => $val) {
                if (!isset($temp[$key])) {
                    unset($data[$table][$key]);
                }
            }
        }
        $hash_old = $row["hash"];
        $datetime_old = $row["datetime"];
        $version_old = $row["ver_id"];
    }
    return $data;
}
