<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 *
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
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
                if (isset($field['#attr']['fkey']) && $field['#attr']['fkey'] === $table) {
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
