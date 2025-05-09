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
 * Version helper module
 *
 * This fie contains useful functions related to the control and version system, they allow to
 * relationate registers with users and groups, and to add and retrieve the versions of a register
 */

/**
 * Make Version function
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
 * @app    => code of the application that you want to add a new version
 * @reg_id => register of the app that you want to add a new version
 *
 * Notes:
 *
 * This function returns an integer as response about the control action:
 *
 * +1 => insert executed, this is because the app register exists and they can add a new version register
 * -1 => app not found, this is because the app requested not have a table in the apps config
 * -2 => version table not found, this is because the has_version feature is disabled by dbstatic
 * -3 => data not found and version not found, app register not exists and version register not exists
 * -4 => data not found but version found, app register not exists and version register exists
 *
 * As you can see, negative values denotes an error and positive values denotes a successfully situation
 */
function make_version($app, $reg_id)
{
    // Check the passed parameters
    $table = app2table($app);
    if ($table == '') {
        return -1;
    }
    // Check if version exists
    $query = "SELECT id FROM {$table}_version LIMIT 1";
    if (!db_check($query)) {
        return -2;
    }
    // Sets the semaphore
    $semaphore = [$app, $reg_id];
    if (!semaphore_acquire($semaphore)) {
        return [T('Could not acquire the semaphore')];
    }
    // Check if exists data in the version table
    $query = "SELECT MAX(id) FROM {$table}_version WHERE reg_id = ?";
    $version_id = execute_query($query, [$reg_id]);
    // Check if exists data in the main table
    $query = "SELECT id FROM $table WHERE id = ?";
    $data_id = execute_query($query, [$reg_id]);
    if (!$data_id) {
        if (!$version_id) {
            semaphore_release($semaphore);
            return -3;
        } else {
            semaphore_release($semaphore);
            return -4;
        }
    }
    // Compute the diff from old data and new data
    $data_old = get_version($app, $reg_id, INF);
    if (!is_array($data_old)) {
        if ($data_old != -3) {
            show_php_error(['phperror' => "Internal error for $app:$reg_id"]);
        }
        $data_old = [];
    }
    $query = "SELECT * FROM $table WHERE id = ?";
    $data_new = [];
    $data_new[$table] = [];
    $data_new[$table][$reg_id] = execute_query($query, [$reg_id]);
    // Add the data from subtables, if exists
    $subtables = app2subtables($app);
    // Add the control table to subtables, if apply
    $subtable = "{$table}_control";
    $query = "SELECT id FROM $subtable LIMIT 1";
    if (db_check($query)) {
        $subtables[] = [
            'subtable' => $subtable,
            'field' => 'id',
        ];
    }
    // Add the files table to subtables, if apply
    $subtable = "{$table}_files";
    $query = "SELECT id FROM $subtable LIMIT 1";
    if (db_check($query)) {
        $subtables[] = [
            'subtable' => $subtable,
            'field' => 'reg_id',
        ];
    }
    // Add the notes table to subtables, if apply
    $subtable = "{$table}_notes";
    $query = "SELECT id FROM $subtable LIMIT 1";
    if (db_check($query)) {
        $subtables[] = [
            'subtable' => $subtable,
            'field' => 'reg_id',
        ];
    }
    // And now, process subtables
    foreach ($subtables as $temp) {
        $subtable = $temp['subtable'];
        $field = $temp['field'];
        $query = "SELECT * FROM $subtable WHERE $field = ?";
        $data_new[$subtable] = [];
        $rows = execute_query_array($query, [$reg_id]);
        foreach ($rows as $key => $val) {
            // Ignote the search field in the files tables
            if ($subtable == "{$table}_files") {
                unset($val['search']);
            }
            // Continue;
            $data_new[$subtable][$val['id']] = $val;
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
    $user_id = current_user();
    $datetime = current_datetime();
    $table = app2table($app);
    $query = "SELECT hash FROM {$table}_version WHERE id = ?";
    $hash_old = strval(execute_query($query, [$version_id]));
    $query = "SELECT ver_id FROM {$table}_version WHERE id = ?";
    $ver_id = intval(execute_query($query, [$version_id]));
    // Prepare the array to the insert
    $array = [
        'user_id' => $user_id,
        'datetime' => $datetime,
        'reg_id' => $reg_id,
        'ver_id' => $ver_id + 1,
        'data' => base64_encode(serialize($data)),
        'hash' => $hash_old,
    ];
    // Update the hash with the new hash to do the blockchain
    $array['hash'] = md5(serialize($array));
    // Do the insert of the new version
    $query = prepare_insert_query("{$table}_version", $array);
    db_query(...$query);
    semaphore_release($semaphore);
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
 *
 * This function returns an array or an integer as response about the control action:
 *
 * -1 => app not found, this is because the app requested not have a table in the apps config
 * -2 => version table not found, this is because the has_version feature is disabled by dbstatic
 * -3 => data not found, this is because the version requested not exists
 *
 * As you can see, negative values denotes an error and positive values denotes a successfully situation
 */
function get_version($app, $reg_id, $ver_id = null)
{
    // Check the passed parameters
    $table = app2table($app);
    if ($table == '') {
        return -1;
    }
    // Check if version exists
    $query = "SELECT id FROM {$table}_version LIMIT 1";
    if (!db_check($query)) {
        return -2;
    }
    $query = "SELECT * FROM {$table}_version WHERE reg_id = ? ORDER BY id ASC";
    $rows = execute_query_array($query, [$reg_id]);
    $data = [];
    $hash_old = '';
    $datetime_old = '';
    $version_old = 0;
    $result = [];
    foreach ($rows as $row) {
        // This guarantees that the id fields are integers
        $row['user_id'] = intval($row['user_id']);
        $row['reg_id'] = intval($row['reg_id']);
        $row['ver_id'] = intval($row['ver_id']);
        // Check the blockchain integrity
        $array = [
            'user_id' => $row['user_id'],
            'datetime' => $row['datetime'],
            'reg_id' => $row['reg_id'],
            'ver_id' => $row['ver_id'],
            'data' => $row['data'],
            'hash' => $hash_old,
        ];
        if ($row['hash'] != md5(serialize($array))) {
            $ver_id = $row['ver_id'];
            show_php_error(['phperror' => "Blockchain integrity break for $app:$reg_id:$ver_id"]);
        }
        // Check other vars that must accomplish that new values are greather or equal that old values
        if ($row['datetime'] < $datetime_old) {
            $ver_id = $row['ver_id'];
            show_php_error(['phperror' => "Blockchain integrity break for $app:$reg_id:$ver_id"]);
        }
        if ($row['ver_id'] != $version_old + 1) {
            $ver_id = $row['ver_id'];
            show_php_error(['phperror' => "Blockchain integrity break for $app:$reg_id:$ver_id"]);
        }
        // Merge the new data with the data of the previous versions
        $data_new = unserialize(base64_decode($row['data']));
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
        $hash_old = $row['hash'];
        $datetime_old = $row['datetime'];
        $version_old = $row['ver_id'];
        // Store data in result
        $result[$row['ver_id']] = $data;
    }
    if ($ver_id === null) {
        return $result;
    }
    if ($ver_id === INF) {
        $ver_id = $version_old;
    }
    if (!isset($result[$ver_id])) {
        return -3;
    }
    return $result[$ver_id];
}

/**
 * Delete Version function
 *
 * This function allow to delete the last version to a reg_id of an app, to do it,
 * the function requires to specify the app and reg_id
 *
 * @app    => code of the application that you want to add a new version
 * @reg_id => register of the app that you want to add a new version
 *
 * Notes:
 *
 * This function returns an integer as response about the control action:
 *
 * +1 => delete executed, this is because the app register exists and they can delete the last register
 * -1 => app not found, this is because the app requested not have a table in the apps config
 * -2 => version table not found, this is because the has_version feature is disabled by dbstatic
 * -3 => data not found, this is because the app register not exists and the version register not exists
 *
 * As you can see, negative values denotes an error and positive values denotes a successfully situation
 */
function del_version($app, $reg_id)
{
    // Check the passed parameters
    $table = app2table($app);
    if ($table == '') {
        return -1;
    }
    // Check if version exists
    $query = "SELECT id FROM {$table}_version LIMIT 1";
    if (!db_check($query)) {
        return -2;
    }
    $query = "SELECT MAX(id) FROM {$table}_version WHERE reg_id = ?";
    $version_id = execute_query($query, [$reg_id]);
    // Check if exists data in the main table
    if (!$version_id) {
        return -3;
    }
    // Do the delete of the last version
    $query = "DELETE FROM {$table}_version WHERE id = ?";
    db_query($query, [$version_id]);
    return 1;
}
