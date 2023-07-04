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
 * Add list actions helper
 *
 * This function returns the input rows adding to each row an array of actions,
 * to do this function uses the permissions to check that each register and each
 * action is allowed by the token
 *
 * @rows => the rows used to do the list
 * @actions => an array with the actions that want to be added in each row
 *
 * Notes:
 *
 * This function add to each row the actions using the permissions of the system
 * associated to the current user, for each permission and using the new owner
 * concept
 */
function add_list_actions($rows, $actions)
{
    foreach ($rows as $key => $row) {
        $row["actions"] = array();
        foreach ($actions as $action) {
            $table = app2table($action["app"]);
            $id = $row["id"];
            $sql = check_sql($action["app"], $action["action"]);
            $query = "SELECT id FROM $table WHERE id=$id AND $sql";
            $has_perm = execute_query($query);
            if ($has_perm) {
                $action["url"] = "app/{$action["app"]}/{$action["action"]}/{$row["id"]}";
            } else {
                $action["url"] = "";
            }
            $row["actions"][] = $action;
        }
        $rows[$key] = $row;
    }
    return $rows;
}
