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
 * Dependencies feature
 *
 * This file contains the code that has the ability to identify dependencies
 */

/**
 * Check Dependencies
 *
 * This function allow to get all dependencies of an app and id, intended to check
 * the if the sytem can delete some register or if the register has relations with
 * other apps or tables
 */
function check_dependencies($app, $id)
{
    require_once 'php/lib/dbschema.php';
    $dbschema = eval_attr(xmlfiles2array(detect_apps_files('xml/dbschema.xml')));
    $dbschema = __dbschema_auto_apps($dbschema);
    $dbschema = __dbschema_auto_fkey($dbschema);
    $dbschema = __dbschema_auto_name($dbschema);
    $table = app2table($app);
    $result = [];
    if (is_array($dbschema) && isset($dbschema['tables']) && is_array($dbschema['tables'])) {
        foreach ($dbschema['tables'] as $tablespec) {
            if (isset($tablespec['#attr']['ignore']) && eval_bool($tablespec['#attr']['ignore'])) {
                continue;
            }
            foreach ($tablespec['value']['fields'] as $field) {
                if (isset($field['#attr']['fkey']) && $field['#attr']['fkey'] == $table) {
                    $result[] = [
                        'table' => $tablespec['#attr']['name'],
                        'field' => $field['#attr']['name'],
                    ];
                }
            }
        }
    }
    foreach ($result as $key => $val) {
        $deptable = $val['table'];
        $depfield = $val['field'];
        $query = "SELECT COUNT(*) FROM $deptable WHERE $depfield=$id";
        $numrows = execute_query($query);
        if (!$numrows) {
            unset($result[$key]);
            continue;
        }
        $result[$key]['query'] = $query;
        $result[$key]['count'] = $numrows;
        if (table_exists($deptable)) {
            $result[$key]['app'] = table2app($deptable);
        } elseif (subtable_exists($deptable)) {
            $result[$key]['app'] = subtable2app($deptable);
        } else {
            $temp = str_replace([
                '_index', '_control', '_version', '_files', '_notes', '_log',
            ], '', $deptable);
            if (table_exists($temp)) {
                $result[$key]['app'] = table2app($temp);
            }
        }
    }
    return $result;
}
