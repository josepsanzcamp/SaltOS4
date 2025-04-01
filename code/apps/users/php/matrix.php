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
 * Matrix functions
 *
 * This file contains all the functions required by the Excel widget for handling
 * permissions and applications in a matrix structure.
 */

/**
 * Create matrix data
 *
 * This function generates a matrix of permissions and applications, associating each
 * application with its respective permissions and their assigned values ("Allow", "Deny", or "").
 */
function make_matrix_data($perms, $apps, $main, $user)
{
    $perms = array_flip($perms);
    $apps = array_flip($apps);

    $matrix = [];
    // Initialize the matrix with empty values
    foreach ($apps as $app_id => $app_pos) {
        foreach ($perms as $perm_id => $perm_pos) {
            if (!isset($matrix[$app_pos])) {
                $matrix[$app_pos] = [];
            }
            $matrix[$app_pos][$perm_pos] = '';
        }
    }
    //~ print_r($matrix);
    //~ die();

    // Populate the matrix with main data
    $main = array_protected($main);
    foreach ($main as $cell) {
        if ($cell['deny']) {
            $value = 'Deny';
        } elseif ($cell['allow']) {
            $value = 'Allow';
        } else {
            $value = '';
        }
        $app_pos = $apps[$cell['app_id']];
        $perm_pos = $perms[$cell['perm_id']];
        $matrix[$app_pos][$perm_pos] = $value;
        //~ print_r($cell);
        //~ die();
    }

    // Populate the matrix with user data
    $user = array_protected($user);
    foreach ($user as $cell) {
        if ($cell['deny']) {
            $value = 'Deny';
        } elseif ($cell['allow']) {
            $value = 'Allow';
        } else {
            $value = '';
        }
        $app_pos = $apps[$cell['app_id']];
        $perm_pos = $perms[$cell['perm_id']];
        $matrix[$app_pos][$perm_pos] = $value;
        //~ print_r($cell);
        //~ die();
    }

    //~ print_r($matrix);
    //~ die();
    return $matrix;
}

/**
 * Create matrix cells
 *
 * This function generates matrix cells with additional attributes such as column and row indices,
 * and dropdown options for editing cell values. Read-only attributes are applied to specific cells.
 */
function make_matrix_cell($perms, $apps, $main, $user)
{
    $perms = array_flip($perms);
    $apps = array_flip($apps);

    $matrix = [];
    // Initialize the matrix with cell metadata
    foreach ($apps as $app_id => $app_pos) {
        foreach ($perms as $perm_id => $perm_pos) {
            if (!isset($matrix[$app_pos])) {
                $matrix[$app_pos] = [];
            }
            $matrix[$app_pos][$perm_pos] = [
                'col' => $perm_pos,
                'row' => $app_pos,
                'readOnly' => true,
            ];
        }
    }
    //~ print_r($matrix);
    //~ die();

    // Update cell attributes with main data
    $main = array_protected($main);
    foreach ($main as $cell) {
        $perm_pos = $perms[$cell['perm_id']];
        $app_pos = $apps[$cell['app_id']];
        if ($cell['deny']) {
            $matrix[$app_pos][$perm_pos] = [
                'col' => $perm_pos,
                'row' => $app_pos,
                'type' => 'dropdown',
                'source' => ['Allow', 'Deny'],
                'readOnly' => true,
            ];
        } else {
            $matrix[$app_pos][$perm_pos] = [
                'col' => $perm_pos,
                'row' => $app_pos,
                'type' => 'dropdown',
                'source' => ['Allow', 'Deny'],
            ];
        }
        //~ print_r($cell);
        //~ die();
    }

    // Update cell attributes with user data
    $user = array_protected($user);
    foreach ($user as $cell) {
        $perm_pos = $perms[$cell['perm_id']];
        $app_pos = $apps[$cell['app_id']];
        $matrix[$app_pos][$perm_pos] = [
            'col' => $perm_pos,
            'row' => $app_pos,
            'type' => 'dropdown',
            'source' => ['Allow', 'Deny'],
        ];
        //~ print_r($cell);
        //~ die();
    }

    $matrix = array_merge(...$matrix);
    //~ print_r($matrix);
    //~ die();

    return $matrix;
}

/**
 * Unmake matrix data
 *
 * This function reverses the matrix data into a more manageable format by applying
 * permission and application mappings while validating data consistency.
 */
function unmake_matrix_data($perms, $apps, $main, $json)
{
    if ($json === null) {
        return $json;
    }

    // Reorganize the main data for easier access
    foreach ($main as $key => $val) {
        unset($main[$key]);
        $key = $val['app_id'] . '|' . $val['perm_id'];
        $main[$key] = $val;
    }

    $matrix = [];
    $json = array_protected($json);
    // Process JSON data into the matrix
    foreach ($json as $app_pos => $temp) {
        foreach ($temp as $perm_pos => $val) {
            $app_id = $apps[$app_pos];
            $perm_id = $perms[$perm_pos];
            $allow = 0;
            $deny = 0;
            switch ($val) {
                case 'Allow':
                    $allow = 1;
                    $deny = 0;
                    break;
                case 'Deny':
                    $allow = 0;
                    $deny = 1;
                    break;
                case '':
                    $allow = 0;
                    $deny = 0;
                    break;
                default:
                    show_json_error("Unknown value $val");
            }
            $key = "$app_id|$perm_id";
            if (isset($main[$key])) {
                $case = $main[$key]['allow'] . $main[$key]['deny'] . $allow . $deny;
                if (in_array($case, ['0010', '0001', '1001'])) {
                    $matrix[] = [
                        'app_id' => $app_id,
                        'perm_id' => $perm_id,
                        'allow' => $allow,
                        'deny' => $deny,
                    ];
                }
            }
        }
    }

    //~ print_r($matrix);
    //~ die();
    return $matrix;
}

/**
 * Generate matrix permissions
 *
 * This function generates the complete matrix data and metadata for use in
 * the Excel widget, including column headers, row headers, data values, and cell details.
 */
function make_matrix_perms($table, $field, $id)
{
    $perms = execute_query_array("SELECT id, CONCAT_WS('/',code,NULLIF(owner,'')) code
        FROM tbl_perms WHERE active = 1 ORDER BY id ASC");
    $apps = execute_query_array('SELECT id, code FROM tbl_apps WHERE active = 1 ORDER BY id ASC');
    $perms_code = array_column($perms, 'code');
    $apps_code = array_column($apps, 'code');
    $perms_id = array_column($perms, 'id');
    $apps_id = array_column($apps, 'id');
    $apps_perms = execute_query_array('SELECT * FROM tbl_apps_perms');
    $reg_apps_perms = execute_query_array("SELECT * FROM $table WHERE $field = ?", [$id]);
    $data = make_matrix_data($perms_id, $apps_id, $apps_perms, $reg_apps_perms);
    $cell = make_matrix_cell($perms_id, $apps_id, $apps_perms, $reg_apps_perms);
    return [
        'colHeaders' => $perms_code,
        'rowHeaders' => $apps_code,
        'data' => $data,
        'cell' => $cell,
    ];
}
