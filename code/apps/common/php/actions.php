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
 * TODO
 *
 * TODO
 */

/**
 * Merge data actions
 *
 * This function merge the rows of a table or list with the specified actions
 *
 * @data    => the data of the table or list widget
 * @actions => the desired actions to use in the table or list widget
 */
function __merge_data_actions($data, $actions)
{
    // Prepare the actions
    if (!is_array($actions)) {
        show_php_error(['phperror' => 'actions must be an array']);
    }
    foreach ($actions as $key => $action) {
        $action = join_attr_value($action);
        $action = eval_attr($action);
        $action0 = get_part_from_string($action['action'], '/', 0);
        if (__app_has_perm($action['app'], $action0)) {
            $actions[$key] = $action;
        } else {
            unset($actions[$key]);
        }
    }
    // Add the actions to each row checking each permissions's row
    foreach ($data as $key => $row) {
        $merge = [];
        foreach ($actions as $action) {
            $action1 = get_part_from_string($action['action'], '/', -1);
            $action['arg'] = "app/{$action["app"]}/{$action["action"]}/{$row["id"]}";
            unset($action['app']);
            unset($action['action']);
            $merge[$action1] = $action;
        }
        if (count($merge)) {
            $data[$key]['actions'] = $merge;
        }
    }
    return $data;
}
