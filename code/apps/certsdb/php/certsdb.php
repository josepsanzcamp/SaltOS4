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
function __certsdb_list($search, $offset, $limit)
{
    require_once 'php/lib/nssdb.php';
    $list = __nssdb_list();

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
        $list = __nssdb_grep_helper($list, $val, $type == '-');
    }

    // Implement the offset and limit feature
    if ($limit !== INF) {
        $list = array_slice($list, $offset, $limit);
    }

    // Returns the list with two items: id and name
    foreach ($list as $key => $val) {
        $list[$key] = ['id' => md5($val), 'name' => $val];
    }

    return $list;
}

/**
 * TODO
 *
 * TODO
 */
function __certsdb_insert($json)
{
    require_once 'php/lib/upload.php';
    require_once 'php/lib/nssdb.php';
    __nssdb_init();

    $upload = get_directory('dirs/uploaddir') ?? getcwd_protected() . '/data/upload/';
    $certs = 0;
    foreach ($json['certfile'] as $file) {
        if (
            check_upload_file([
                'user_id' => current_user(),
                'uniqid' => $file['id'],
                'app' => $file['app'],
                'name' => $file['name'],
                'size' => $file['size'],
                'type' => $file['type'],
                'file' => $file['file'],
                'hash' => $file['hash'],
            ])
        ) {
            $output = __nssdb_add($upload . $file['file'], $json['passfile']);
            if (implode('', $output) == 'pk12util: PKCS12 IMPORT SUCCESSFUL') {
                $certs++;
            }
        }
    }
    $count = count($json['certfile']);
    if ($count != $certs) {
        return [
            'status' => 'ko',
            'text' => 'Error importing certificates',
            'code' => __get_code_from_trace(),
        ];
    }
    foreach ($json['certfile'] as $file) {
        del_upload_file($file);
    }
    return [
        'status' => 'ok',
    ];
}

/**
 * TODO
 *
 * TODO
 */
function __certsdb_hash2nick($hash)
{
    require_once 'php/lib/nssdb.php';
    $list = __nssdb_list();
    foreach ($list as $key => $val) {
        if ($hash == md5($val)) {
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
function __certsdb_check($hash)
{
    if (__certsdb_hash2nick($hash)) {
        return true;
    }
    return false;
}

/**
 * TODO
 *
 * TODO
 */
function __certsdb_view($hash)
{
    $nick = __certsdb_hash2nick($hash);
    if ($nick == '') {
        return [
            'status' => 'ko',
            'text' => 'Nick not found',
            'code' => __get_code_from_trace(),
        ];
    }
    $info = __nssdb_info($nick);
    $info = array_map(fn($k, $v) => "$k: $v", array_keys($info), $info);
    $info = implode("\n", $info);
    return [
        'name' => $nick,
        'info' => $info,
    ];
}

/**
 * TODO
 *
 * TODO
 */
function __certsdb_delete($hash)
{
    $nick = __certsdb_hash2nick($hash);
    if ($nick == '') {
        return [
            'status' => 'ko',
            'text' => 'Nick not found',
            'code' => __get_code_from_trace(),
        ];
    }
    $info = __nssdb_remove($nick);
    return [
        'status' => 'ok',
    ];
}
