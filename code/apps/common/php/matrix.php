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
 * version and log data in a matrix format.
 */

/**
 * Create matrix for version data
 *
 * This function generates a matrix structure for displaying version-related data
 * in the Excel widget, including headers, data formatting, and cell attributes.
 */
function make_matrix_version($app, $id)
{
    // Retrieve the corresponding table for the application
    $table = app2table($app);

    // Fetch version data using the required library
    require_once 'php/lib/version.php';
    $data = array_values(get_version($app, $id));

    // Compute headers for versions to be displayed in the sheet
    $query = "SELECT (SELECT name FROM tbl_users b WHERE b.id=user_id) user, datetime, ver_id
        FROM {$table}_version WHERE reg_id = ? ORDER BY id ASC";
    $versions = execute_query_array($query, [$id]);
    foreach ($versions as $key => $val) {
        $val['user'] = $val['user'] ?? '-';
        $val['datetime'] = datetime_format($val['datetime']);
        $val['ver_id'] = 'v' . $val['ver_id'];
        $versions[$key] = implode('<br/>', $val);
    }

    // Convert tree-like data into a flat matrix format
    foreach ($data as $key => $val) {
        $temp = [];
        foreach ($val as $key2 => $val2) {
            foreach ($val2 as $key3 => $val3) {
                foreach ($val3 as $key4 => $val4) {
                    $temp["$key2.$key3.$key4"] = $val4;
                }
            }
        }
        $data[$key] = $temp;
    }

    // Generate headers with unique table, ID, and field names
    $headers = [];
    foreach ($data as $key => $val) {
        $headers = array_merge($headers, array_keys($val));
    }
    $headers = array_keys(array_flip($headers));

    // Fill matrix data using headers as keys
    foreach ($data as $key => $val) {
        $temp = array_fill_keys($headers, '');
        $temp = array_replace($temp, $val);
        $temp = array_values($temp);
        $data[$key] = $temp;
    }

    // Transpose matrix dimensions
    $data = array_transpose($data);

    // Compute ranges for colorization based on headers
    $ranges = [];
    $old = [];
    $pos = [];
    $key = null;
    foreach ($headers as $key => $val) {
        $temp = explode('.', $val);
        unset($temp[2]);
        $temp = implode('.', $temp);
        if (!count($old)) {
            $old['key'] = $key;
            $old['val'] = $temp;
            $pos[] = $key;
            continue;
        }
        if ($old['val'] != $temp) {
            $pos[] = $old['key'];
            $ranges[] = $pos;
            $old['key'] = $key;
            $old['val'] = $temp;
            $pos = [$key];
            continue;
        }
        $old['key'] = $key;
        $old['val'] = $temp;
    }
    if (count($pos) == 1) {
        $pos[] = $key;
        $ranges[] = $pos;
    }

    // Simplify headers by removing table and ID information
    foreach ($headers as $key => $val) {
        $temp = explode('.', $val);
        $headers[$key] = $temp[2];
    }

    // Apply color schemes to matrix cells based on ranges
    $matrix = [];
    $colors = [
        'bg-primary-subtle text-black',
        //~ 'bg-secondary-subtle text-black',
        'bg-success-subtle text-black',
        'bg-danger-subtle text-black',
        'bg-warning-subtle text-black',
        'bg-info-subtle text-black',
        //~ 'bg-light-subtle text-black',
        //~ 'bg-dark-subtle text-black',
    ];
    foreach ($ranges as $key => $val) {
        $color = $colors[$key % count($colors)];
        for ($i = $val[0]; $i <= $val[1]; $i++) {
            for ($j = 0; $j < count($versions); $j++) {
                $matrix[$i][$j] = [
                    'col' => $j,
                    'row' => $i,
                    'className' => $color,
                ];
            }
        }
    }

    // Add diff-based colorization to matrix cells
    $val0 = null;
    foreach ($data as $key => $val) {
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
                    $color = 'bg-danger text-white';
                }
                $matrix[$key][$key2] = [
                    'col' => $key2,
                    'row' => $key,
                    'className' => $color,
                ];
            }
            $val0 = $val2;
        }
    }

    // Add text-break class to all cells for improved readability
    foreach ($data as $key => $val) {
        foreach ($val as $key2 => $val2) {
            $matrix[$key][$key2]['className'] .= ' text-break';
        }
    }

    // Compute column widths for headers and data using font metrics
    require_once 'php/lib/gdlib.php';
    $widths = [0];
    $size = 12;
    $margin = 20;
    foreach ($headers as $key => $val) {
        $width = compute_width(strval($val), $size);
        $widths[0] = max($widths[0], $width + $margin);
    }
    foreach ($versions as $key => $val) {
        $val = str_replace('<br/>', "\n", $val);
        $width = compute_width($val, $size);
        $widths[$key + 1] = $width + $margin;
    }
    foreach ($data as $key => $val) {
        foreach ($val as $key2 => $val2) {
            $width = compute_width(strval($val2), $size);
            $widths[$key2 + 1] = max($widths[$key2 + 1], $width + $margin);
        }
    }
    $maxwidth = 500;
    foreach ($widths as $key => $val) {
        $widths[$key] = min($maxwidth, $val);
    }

    // Convert matrix to single-dimensional array
    $matrix = array_merge(...$matrix);

    // Return the Excel field with headers, data, and formatting information
    return [
        'colHeaders' => $versions,
        'rowHeaders' => $headers,
        'data' => $data,
        'cell' => $matrix,
        'rowHeaderWidth' => $widths[0],
        'colWidths' => array_slice($widths, 1),
    ];
}

/**
 * Create matrix for log data
 *
 * This function processes log data and generates headers and formatting information
 * for integration into the Excel widget.
 */
function make_matrix_log($app, $id)
{
    // Retrieve the corresponding table for the application
    $table = app2table($app);

    // Fetch logs using the required library
    require_once 'php/lib/log.php';
    $data = get_logs($app, $id);
    $users = [];

    // Process logs and associate user names with entries
    foreach ($data as $key => $val) {
        $user_id = $val['user_id'];
        unset($val['id']);
        unset($val['user_id']);
        if (!isset($users[$user_id])) {
            $users[$user_id] = execute_query('SELECT name FROM tbl_users WHERE id = ?', [$user_id]);
        }
        $val = array_merge(['user' => $users[$user_id]], $val);
        $val['datetime'] = datetime_format($val['datetime']);
        $data[$key] = $val;
    }

    // Generate column headers for the log matrix
    $headers = array_combine(array_keys($data[0]), array_keys($data[0]));
    foreach ($headers as $key => $val) {
        $headers[$key] = ucwords(str_replace('_', ' ', $val));
    }

    // Compute column widths for headers and data using font metrics
    require_once 'php/lib/gdlib.php';
    $widths = [0];
    $size = 12;
    $margin = 20;
    foreach ($headers as $key => $val) {
        $width = compute_width($val, $size);
        $widths[$key] = $width + $margin;
    }
    foreach ($data as $key => $val) {
        foreach ($val as $key2 => $val2) {
            $width = compute_width(strval($val2), $size);
            $widths[$key2] = max($widths[$key2], $width + $margin);
        }
    }
    $maxwidth = 1000;
    foreach ($widths as $key => $val) {
        $widths[$key] = min($maxwidth, $val);
    }

    // Return the Excel field
    return [
        'colHeaders' => array_values($headers),
        'data' => $data,
        'colWidths' => array_slice(array_values($widths), 1),
    ];
}
