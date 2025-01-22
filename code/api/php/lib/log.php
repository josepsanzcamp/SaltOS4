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
 * @app    => code of the application where you want to add the log
 * @reg_id => register of the app where you want to add the log
 * @log    => the log message that you want to add to the log register
 *
 * Notes:
 *
 * This function returns an integer as response about the control action:
 *
 * +1 => insert executed, this is because the app register exists and the control register not exists
 * -1 => app not found, this is because the app requested not have a table in the apps config
 * -2 => log table not found, this is because the has_log feature is disabled by dbstatic
 */
function make_log($app, $reg_id, $log)
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
    $query = prepare_insert_query("{$table}_log", [
        'user_id' => $user_id,
        'datetime' => $datetime,
        'log' => $log,
        'reg_ids' => check_ids($reg_id),
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
function make_log_bypass($app, $data, $log)
{
    if (app2log($app)) {
        if (isset($data['id'])) {
            make_log($app, $data['id'], $log);
        } else {
            $ids = array_column($data, 'id');
            if (count($ids)) {
                make_log($app, $ids, $log);
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
    $query = "SELECT id, user_id, datetime, log, reg_ids
        FROM {$table}_log WHERE FIND_IN_SET(?, reg_ids) ORDER BY id ASC";
    $rows = execute_query_array($query, [$reg_id]);
    return $rows;
}
