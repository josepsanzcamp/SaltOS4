<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2024 by Josep Sanz Campderrós
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
 * This file contain all functions needed by the excel widget
 */

/**
 * TODO
 *
 * TODO
 */
function make_matrix_version($app, $id)
{
    // the table and fields are the hidden fields of files tables
    $table = app2table($app);
    $files_table = "{$table}_files";
    $files_fields = ['uniqid', 'file', 'hash', 'search', 'indexed', 'retries'];
    // continue retrieving versions data
    require_once 'php/lib/control.php';
    $data = get_version($app, $id);
    $query = "SELECT CONCAT(
        IFNULL((SELECT name FROM tbl_users b WHERE b.id=user_id), ''),
        '<br/>', datetime, '<br/>v', ver_id
        ) FROM {$table}_version WHERE reg_id = ? ORDER BY id ASC";
    $versions = execute_query_array($query, [$id]);
    // join table with id with field name
    // and convert the tree into a matrix
    foreach ($data as $key => $val) {
        $temp = [];
        foreach ($val as $key2 => $val2) {
            foreach ($val2 as $key3 => $val3) {
                foreach ($val3 as $key4 => $val4) {
                    // hide the main id field of all tables
                    if ($key4 == 'id') {
                        continue;
                    }
                    // hide some files fields
                    if ($key2 == $files_table && in_array($key4, $files_fields)) {
                        continue;
                    }
                    $temp["$key2.$key3.$key4"] = $val4;
                }
            }
        }
        $data[$key] = $temp;
    }
    // compute header with all unique table.id.field
    $header = [];
    foreach ($data as $key => $val) {
        $header = array_merge($header, array_keys($val));
    }
    $header = array_keys(array_flip($header));
    // create a filled matrix using as key the header
    foreach ($data as $key => $val) {
        $temp = array_fill_keys($header, '');
        $temp = array_replace($temp, $val);
        $temp = array_values($temp);
        $data[$key] = $temp;
    }
    // change the dimensions of the matrix
    $data = array_transpose($data);
    // remove the table and id from header
    foreach ($header as $key => $val) {
        $temp = explode('.', $val);
        $header[$key] = $temp[2];
    }
    // define colors
    $matrix = [];
    foreach ($data as $key => $val) {
        // Convert keys from 1..n to 0..n-1
        $val = array_values($val);
        foreach ($val as $key2 => $val2) {
            if (!$key2) {
                // first iteration, only store val0
                $val0 = $val2;
                continue;
            }
            if ($val2 != $val0) {
                if ($val2 != '') {
                    $color = 'bg-success text-white';
                } else {
                    $color = 'bg-danger';
                }
                $matrix[] = [
                    'col' => $key2,
                    'row' => $key,
                    'className' => $color,
                ];
            }
            $val0 = $val2;
        }
    }
    // return the excel field
    return [
        'numcols' => count($versions),
        'numrows' => count($header),
        'colHeaders' => $versions,
        'rowHeaders' => $header,
        'data' => $data,
        'cell' => $matrix,
    ];
}
