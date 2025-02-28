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
 * TODO
 *
 * TODO
 */
function __files_list($search, $offset, $limit)
{
    $list = glob('data/logs/*');
    // Implement the search feature
    $search = explode_with_quotes(' ', $search);
    foreach ($search as $key => $val) {
        $val = get_string_from_quotes($val);
        $type = '+';
        while (isset($val[0]) && in_array($val[0], ['+', '-'])) {
            $type = $val[0];
            $val = substr($val, 1);
        }
        $val = get_string_from_quotes($val);
        if (!strlen($val)) {
            continue;
        }
        $list = array_grep($list, $val, $type == '-');
    }

    // Implement the offset and limit feature
    if ($limit !== INF) {
        $list = array_slice($list, $offset, $limit);
    }

    // Returns the list with two items: id and name
    foreach ($list as $key => $val) {
        $list[$key] = [
            'id' => basename($val),
            'name' => basename($val),
            'size' => get_human_size(filesize($val), ' ', 'bytes'),
            'type' => saltos_content_type($val),
        ];
    }

    return $list;
}

/**
 * TODO
 *
 * TODO
 */
function __files_getfile($file)
{
    $list = glob('data/logs/*');
    foreach ($list as $key => $val) {
        if (basename($val) == $file) {
            return $val;
        }
    }
    return '';
}

/**
 * TODO
 *
 * TODO
 */
function __files_check($file)
{
    if (__files_getfile($file)) {
        return true;
    }
    return false;
}

/**
 * TODO
 *
 * TODO
 */
function __files_view($file)
{
    $file = __files_getfile($file);
    if ($file == '') {
        return [
            'status' => 'ko',
            'text' => 'File not found',
            'code' => __get_code_from_trace(),
        ];
    }
    $buffer = '';
    if (extension($file) == 'gz') {
        $handle = gzopen($file, 'rb');
        $data = gzread($handle, 1024 * 1024 * 10);
        gzclose($handle);
    } else {
        $handle = fopen($file, 'rb');
        $data = fread($handle, 1024 * 1024 * 10);
        fclose($handle);
    }
    return [
        'name' => basename($file),
        'size' => get_human_size(filesize($file), ' ', 'bytes'),
        'type' => saltos_content_type($file),
        'data' => $data,
    ];
}
