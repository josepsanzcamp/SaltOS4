<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2023 by Josep Sanz Campderrós
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
 * Delete files action
 *
 * This file implements the delete files action, requires a POST JSON request
 * with an array of files, and each array must contain the follow entries:
 * id, name, size, type, data, error, file, hash
 *
 * This action checks that not error is found, checks the file element, the
 * size of the file, the hash of the file, and then, remove the file and
 * clear the file and hash element of the array
 *
 * @files => array of files, each element must contain the follow elements:
 * @id    => unique id that is used by the client to identify the response
 * @name  => the name of the file
 * @size  => the size of the file
 * @type  => the type of the file
 * @data  => the contents of the file encoded as inline base64
 * @error => the error in case of errors
 * @file  => this field is used here to put the local filename used in the file
 * @hash  => this field contains the hash of the contents of the file
 */

$user_id = current_user();
if (!$user_id) {
    show_json_error("authentication error");
}

$files = get_data("json/files");
if ($files == "") {
    show_json_error("files not found");
}

foreach ($files as $key => $val) {
    if ($val["error"] != "") {
        continue;
    }
    // Check integrity with the database entry
    $query = "SELECT id FROM tbl_uploads WHERE " . make_where_query([
        "user_id" => $user_id,
        "uniqid" => $val["id"],
        "name" => $val["name"],
        "size" => $val["size"],
        "type" => $val["type"],
        "file" => $val["file"],
        "hash" => $val["hash"],
    ]);
    $id = execute_query($query);
    if (!$id) {
        continue;
    }
    // Check for file name integrity
    if (encode_bad_chars_file($val["file"]) != $val["file"]) {
        continue;
    }
    // Check for file size integrity
    $dir = get_directory("dirs/uploaddir") ?? getcwd_protected() . "/data/upload/";
    if (filesize($dir . $val["file"]) != $val["size"]) {
        continue;
    }
    // Check for file hash integrity
    if (md5_file($dir . $val["file"]) != $val["hash"]) {
        continue;
    }
    // Remove the local file
    unlink($dir . $val["file"]);
    // Remove the database entry
    $query = "DELETE FROM tbl_uploads WHERE id = $id";
    db_query($query);
    // Reset vars
    $val["file"] = "";
    $val["hash"] = "";
    // Update files[key]
    $files[$key] = $val;
}
output_handler_json($files);
