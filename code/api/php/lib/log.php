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
 * Log helper module
 *
 * This fie contains useful functions related to the log feature
 */

/**
 * Make Log function
 *
 * This function adds a log register to the associated log table for each
 * application.
 *
 * @app      => code of the application where you want to add the log
 * @log      => the log message that you want to add to the log register
 * @reg_id   => register ids of the app where you want to add the log
 * @extra_id => extra ids of the app where you want to add the log
 *
 * Notes:
 *
 * This function returns an integer as response about the control action:
 *
 * +1 => insert executed, this is because the app register exists and the control register not exists
 * -1 => app not found, this is because the app requested not have a table in the apps config
 * -2 => log table not found, this is because the has_log feature is disabled by dbstatic
 */
function make_log($app, $log, $reg_ids, $extra_ids = '')
{
    // Check the passed parameters
    $table = app2table($app);
    if ($table == '') {
        return -1;
    }
    // Check if control exists
    $query = "SELECT id FROM {$table}_log LIMIT 1";
    if (!db_check($query)) {
        return -2;
    }
    // Normal operation
    $user_id = current_user();
    $datetime = current_datetime();
    // Prepare reg data
    $reg_array = check_ids_array($reg_ids);
    $reg_count = count($reg_array);
    if ($reg_count == 0) {
        $reg_id = 0;
        $reg_ids = '';
    } elseif ($reg_count == 1) {
        $reg_id = $reg_array[0];
        $reg_ids = '';
    } else {
        $reg_id = 0;
        $reg_ids = implode(',', $reg_array);
    }
    // Prepare extra data
    $extra_array = check_ids_array($extra_ids);
    $extra_count = count($extra_array);
    if ($extra_count == 0) {
        $extra_id = 0;
        $extra_ids = '';
    } elseif ($extra_count == 1) {
        $extra_id = $extra_array[0];
        $extra_ids = '';
    } else {
        $extra_id = 0;
        $extra_ids = implode(',', $extra_array);
    }
    // Continue
    $query = prepare_insert_query("{$table}_log", [
        'user_id' => $user_id,
        'datetime' => $datetime,
        'log' => $log,
        'reg_id' => $reg_id,
        'reg_ids' => $reg_ids,
        'extra_id' => $extra_id,
        'extra_ids' => $extra_ids,
    ]);
    db_query(...$query);
    return 1;
}

/**
 * Make Log Bypass function
 *
 * This function is intended to be used as wrapper between the caller and the
 * execute_query or execute_query_array function, the main idea is to do the
 * same that make_log but uses the reg_id from the array data, and this array
 * can be an array with the contents of one register with an id field, or an
 * array of rows where each item must contain an id field
 *
 * @app  => code of the application where you want to add the log
 * @data => data with the register or registers of the app where you want to
 *          add the log, remember that an id field is needed
 * @log  => the log message that you want to add to the log register
 *
 * Notes:
 *
 * This function always returns the input data
 */
function make_log_bypass($app, $log, $data)
{
    if (app2log($app)) {
        if (isset($data['id'])) {
            make_log($app, $log, $data['id']);
        } else {
            $ids = array_column($data, 'id');
            if (count($ids)) {
                make_log($app, $log, $ids);
            }
        }
    }
    return $data;
}

/**
 * TODO
 *
 * TODO
 */
function get_logs($app, $reg_id)
{
    // Check the passed parameters
    $table = app2table($app);
    if ($table == '') {
        return -1;
    }
    // Check if version exists
    $query = "SELECT id FROM {$table}_log LIMIT 1";
    if (!db_check($query)) {
        return -2;
    }
    $query = "SELECT id, user_id, datetime, log,
        IF(reg_id, reg_id, '') reg_id, IF(extra_id, extra_id, '') extra_id, reg_ids, extra_ids
        FROM {$table}_log WHERE reg_id = ? OR FIND_IN_SET(?, reg_ids) ORDER BY id ASC";
    $rows = execute_query_array($query, [$reg_id, $reg_id]);
    return $rows;
}

/**
 * Delete Log function
 *
 * This function allow to delete the last log to a reg_id of an app, to do it,
 * the function requires to specify the app and reg_id
 *
 * @app    => code of the application that you want to delete the last log
 * @reg_id => register of the app that you want to delete the last log
 *
 * Notes:
 *
 * This function returns an integer as response about the control action:
 *
 * +1 => delete executed, this is because the app register exists and they can delete the last register
 * -1 => app not found, this is because the app requested not have a table in the apps config
 * -2 => log table not found, this is because the has_version feature is disabled by dbstatic
 * -3 => data not found, this is because the app register not exists and the version register not exists
 *
 * As you can see, negative values denotes an error and positive values denotes a successfully situation
 */
function del_log($app, $reg_id)
{
    // Check the passed parameters
    $table = app2table($app);
    if ($table == '') {
        return -1;
    }
    // Check if version exists
    $query = "SELECT id FROM {$table}_log LIMIT 1";
    if (!db_check($query)) {
        return -2;
    }
    $query = "SELECT MAX(id) FROM {$table}_log WHERE reg_id = ?";
    $log_id = execute_query($query, [$reg_id]);
    // Check if exists data in the main table
    if (!$log_id) {
        return -3;
    }
    // Do the delete of the last version
    $query = "DELETE FROM {$table}_log WHERE id = ?";
    db_query($query, [$log_id]);
    return 1;
}
