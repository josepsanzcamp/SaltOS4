<?php

/**
 *  ____        _ _    ___  ____  _  _
 * / ___|  __ _| | |_ / _ \/ ___|| || |
 * \___ \ / _` | | __| | | \___ \| || |_
 *  ___) | (_| | | |_| |_| |___) |__   _|
 * |____/ \__,_|_|\__|\___/|____/   |_|
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
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
function make_matrix_cells($perms, $apps, $main, $user)
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
            // nothing to do
        } else {
            unset($matrix[$app_pos][$perm_pos]);
        }
    }

    // Update cell attributes with user data
    $user = array_protected($user);
    foreach ($user as $cell) {
        $perm_pos = $perms[$cell['perm_id']];
        $app_pos = $apps[$cell['app_id']];
        unset($matrix[$app_pos][$perm_pos]);
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
                if (in_array($case, ['0010', '0001', '1001'], true)) {
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
    $perms = _get_db_perms();
    $apps = _get_db_apps();
    $perms_id = array_column($perms, 'id');
    $apps_id = array_column($apps, 'id');
    $apps_perms = execute_query_array('SELECT * FROM tbl_apps_perms');
    $reg_apps_perms = execute_query_array("SELECT * FROM $table WHERE $field = ?", [$id]);
    $data = make_matrix_data($perms_id, $apps_id, $apps_perms, $reg_apps_perms);
    $cells = make_matrix_cells($perms_id, $apps_id, $apps_perms, $reg_apps_perms);
    return [
        'data' => $data,
        'cells' => $cells,
    ];
}

/**
 * TODO
 */
function _get_db_perms()
{
    static $perms = null;
    if (!$perms) {
        $perms = execute_query_array("SELECT id, CONCAT_WS('/',code,NULLIF(owner,'')) code
            FROM tbl_perms WHERE active = 1 ORDER BY id ASC");
    }
    return $perms;
}

/**
 * TODO
 */
function _get_db_apps()
{
    static $apps = null;
    if (!$apps) {
        $apps = execute_query_array('SELECT id, code FROM tbl_apps WHERE active = 1 ORDER BY id ASC');
    }
    return $apps;
}

/**
 * TODO
 */
function make_matrix_columns()
{
    $perms = _get_db_perms();
    $perms_code = [];
    foreach ($perms as $key => $val) {
        $perms_code[] = [
            'title' => $val['code'],
            'width' => 100,
            'type' => 'dropdown',
            'source' => ['Allow', 'Deny'],
            'autocomplete' => true,
        ];
    }
    return $perms_code;
}

/**
 * TODO
 */
function make_matrix_rows()
{
    $apps = _get_db_apps();
    $apps_code = [];
    foreach ($apps as $key => $val) {
        $apps_code[] = [
            'title' => $val['code'],
        ];
    }
    return $apps_code;
}
