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
 * @app    => code of the application that you want to index
 * @reg_id => register of the app that you want to index
 *
 * Notes:
 *
 * This function returns an integer as response about the control action:
 *
 * +1 => insert executed, this is because the app register exists and the control register not exists
 * +2 => delete executed, this is because the app register not exists and the control register exists
 * -1 => app not found, this is because the app requested not have a table in the apps config
 * -2 => control table not found, this is because the has_control feature is disabled by dbstatic
 * -3 => data not found, this is because the app register not exists and the control register not exists
 * -4 => control exists, this is because the app register exists and the control register too exists
 *
 * As you can see, negative values denotes an error and positive values denotes a successfully situation
 */
function make_control($app, $reg_id)
{
    // Check the passed parameters
    $table = app2table($app);
    if ($table == '') {
        return -1;
    }
    // Check if control exists
    $query = "SELECT id FROM {$table}_control LIMIT 1";
    if (!db_check($query)) {
        return -2;
    }
    // Check if exists data in the control table
    $query = "SELECT id FROM {$table}_control WHERE id = ?";
    $control_id = execute_query($query, [$reg_id]);
    // Check if exists data in the main table
    $query = "SELECT id FROM $table WHERE id = ?";
    $data_id = execute_query($query, [$reg_id]);
    if (!$data_id) {
        if ($control_id) {
            $query = "DELETE FROM {$table}_control WHERE id = ?";
            db_query($query, [$reg_id]);
            return 2;
        } else {
            return -3;
        }
    }
    if (!$control_id) {
        $user_id = current_user();
        $query = 'SELECT group_id FROM tbl_users WHERE id = ?';
        $group_id = execute_query($query, [$user_id]);
        $datetime = current_datetime();
        $query = prepare_insert_query("{$table}_control", [
            'id' => $reg_id,
            'user_id' => $user_id,
            'group_id' => $group_id,
            'datetime' => $datetime,
        ]);
        db_query(...$query);
        return 1;
    } else {
        return -4;
    }
}

/**
 * Integrity
 *
 * This function tries to execute some periodic task intended to fix issues with
 * the integrity of internal relationships, to do it tries to search not found
 * registers in the control table in the first loop and tries to search not found
 * registers in the app table in the second loop, with all found not found registers
 * the function executes the make_control that add or remove the needed control
 * register to maintain the integrity.
 */
function integrity()
{
    $query = "SELECT id,code,`table` FROM tbl_apps WHERE `table`!=''";
    $apps = execute_query_array($query);
    $total = 0;
    foreach ($apps as $app) {
        if (time_get_usage() > get_config('server/percentstop')) {
            break;
        }
        $table = $app['table'];
        // Check if files exists
        $query = "SELECT id FROM {$table}_control LIMIT 1";
        if (!db_check($query)) {
            continue;
        }
        $range = execute_query("SELECT MAX(id) max_id, MIN(id) min_id FROM $table");
        for ($i = $range['min_id']; $i < $range['max_id']; $i += 100000) {
            if (time_get_usage() > get_config('server/percentstop')) {
                break;
            }
            for (;;) {
                if (time_get_usage() > get_config('server/percentstop')) {
                    break;
                }
                // Search ids of the main application table, that doesn't exists on the
                // register table
                $query = "SELECT a.id FROM $table a
                    LEFT JOIN {$table}_control b ON a.id = b.id
                    WHERE b.id IS NULL AND a.id >= ? AND a.id < ? + 100000 LIMIT 1000";
                $ids = execute_query_array($query, [$i, $i]);
                if (!count($ids)) {
                    break;
                }
                foreach ($ids as $id) {
                    make_control($app['code'], $id);
                }
                $total += count($ids);
                if (count($ids) < 1000) {
                    break;
                }
            }
        }
        $range = execute_query("SELECT MAX(id) max_id, MIN(id) min_id FROM {$table}_control");
        for ($i = $range['min_id']; $i < $range['max_id']; $i += 100000) {
            if (time_get_usage() > get_config('server/percentstop')) {
                break;
            }
            for (;;) {
                if (time_get_usage() > get_config('server/percentstop')) {
                    break;
                }
                // Search ids of the register table, that doesn't exists on the
                // main application table
                $query = "SELECT a.id FROM {$table}_control a
                    LEFT JOIN $table b ON b.id = a.id
                    WHERE b.id IS NULL AND a.id >= ? AND a.id < ? + 100000 LIMIT 1000";
                $ids = execute_query_array($query, [$i, $i]);
                if (!count($ids)) {
                    break;
                }
                foreach ($ids as $id) {
                    make_control($app['code'], $id);
                }
                $total += count($ids);
                if (count($ids) < 1000) {
                    break;
                }
            }
        }
    }
    return $total;
}
