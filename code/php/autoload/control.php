<?php

/*
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

/*
 * Make Control function
 *
 * This function allow to insert and delete the control registers associacted
 * to any application and to any register of the application
 *
 * @app => code of the application that you want to index
 * @reg_id => register of the app that you want to index
 * @user_id => user id of the owner of the app register
 * @datetime => time mark used as creation time of the app register
 *
 * Notes:
 *
 * This function allow to pass a null reg_id, this trigger a query that get the
 * last_id used by the table, if the reg_id is an array, the function does
 * a recursive calls to add a control register to all ids of the reg_id array
 *
 * Too, you can pass a null user_id and/or null datetime, in these cases, the
 * function will determine the user_id and datetime automatically
 *
 * This function returns an integer as response about the control action:
 *
 * 1 => insert executed, this is because the app register exists and the indexing register not exists
 * 2 => delete executed, this is because the app register not exists and the indexing register exists
 * -1 => app not found, this is because the app requested not exists in the apps config
 * -2 => app not found, this is because the app requested not have a table in the apps config
 * -3 => control table not found, this is because the has_control feature is disabled by dbstatic
 * -4 => data not found, this is because the app register not exists and the control register too not exists
 * -5 => control exists, this is because the app register exists and the control register too exists
 *
 */
function make_control($app, $reg_id = null, $user_id = null, $datetime = null)
{
   // Check the passed parameters
    $app_id = app2id($app);
    if (!$app_id) {
        return -1;
    }
    $table = app2table($app);
    if ($table == "") {
        return -2;
    }
    if ($reg_id === null) {
        $reg_id = execute_query("SELECT MAX(id) FROM $table");
    }
    if ($user_id === null) {
        $user_id = current_user();
    }
    if ($datetime === null) {
        $datetime = current_datetime();
    }
    if (is_string($reg_id) && strpos($reg_id, ",") !== false) {
        $reg_id = explode(",", $reg_id);
    }
    if (is_array($reg_id)) {
        $result = array();
        foreach ($reg_id as $id) {
            $result[] = make_control($app, $id, $user_id, $datetime);
        }
        return $result;
    }
    // Search if control exists
    $query = "SELECT id FROM ctl_$app WHERE id='$reg_id'";
    if (!db_check($query)) {
        return -3;
    }
    $control_id = execute_query($query);
    // Search if exists data in the main table
    $query = "SELECT id FROM $table WHERE id='$reg_id'";
    $data_id = execute_query($query);
    if (!$data_id) {
        if ($control_id) {
            $query = "DELETE FROM ctl_$app WHERE id='$reg_id'";
            db_query($query);
            return 2;
        } else {
            return -4;
        }
    }
    if ($id_control) {
        return -5;
    } else {
        $query = make_insert_query("ctl_$app", array(
            "id" => $reg_id,
            "user_id" => $user_id,
            "datetime" => $datetime,
        ));
        db_query($query);
        return 1;
    }
}
