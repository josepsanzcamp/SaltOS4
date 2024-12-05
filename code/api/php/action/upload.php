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
 * Add files action
 *
 * This file implements the delete files action, requires a POST JSON request
 * with an array of files, and each array must contain the follow entries:
 * id, name, size, type, data, error, file, hash
 *
 * This action checks that not error is found, get the data and clear the
 * data element of the array, check the prefix of the data using the type,
 * check the size of the data, and then, set the file and hash to the
 * array and store the file in the upload directory
 *
 * This action checks that not error is found, checks the file element, the
 * size of the file, the hash of the file, and then, remove the file and
 * clear the file and hash element of the array
 *
 * @file  => array that contains the follow elements:
 * @id    => unique id that is used by the client to identify the response
 * @app   => the hash, used to know the app from where the file is uploaded
 * @name  => the name of the file
 * @size  => the size of the file
 * @type  => the type of the file
 * @data  => the contents of the file encoded as inline base64
 * @error => the error in case of errors
 * @file  => this field is used here to put the local filename used in the file
 * @hash  => this field contains the hash of the contents of the file
 */

db_connect();
$user_id = current_user();
if (!$user_id) {
    show_json_error('Permission denied');
}

$action = get_data('rest/1');
$file = get_data('json');
if (!count($file)) {
    show_json_error('file not found');
}

require_once 'php/lib/upload.php';

$array = ['id', 'app', 'name', 'size', 'type', 'data', 'error', 'file', 'hash'];
foreach ($array as $key => $val) {
    if (isset($file[$val])) {
        unset($array[$key]);
    }
}
if (count($array)) {
    show_json_error('Missing ' . implode(', ', $array));
}
if ($file['error'] != '') {
    show_json_error($file['error']);
}

// Do the action
switch ($action) {
    case 'addfile':
        $file = add_file($file);
        break;
    case 'delfile':
        $file = del_file($file);
        break;
    default:
        show_php_error(['phperror' => "Unknown action $action"]);
}

output_handler_json($file);
