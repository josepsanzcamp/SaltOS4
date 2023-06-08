<?php

/*
 ____        _ _    ___  ____    _  _    ___
/ ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
\___ \ / _` | | __| | | \___ \  | || |_| | | |
 ___) | (_| | | |_| |_| |___) | |__   _| |_| |
|____/ \__,_|_|\__|\___/|____/     |_|(_)___/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2023 by Josep Sanz Campderrós
More information in https://www.saltos.org or info@saltos.org

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

declare(strict_types=1);

/*
 * About this file
 *
 * This file implements the delete files action, requires a POST JSON request
 * with an array of files, and each array must contain the follow entries:
 * id, name, size, type, data, error, file, hash
 *
 * This action checks that not error is found, get the data and clear the
 * data element of the array, check the prefix of the data using the type,
 * check the size of the data, and then, set the file and hash to the
 * array and store the file in the upload directory
 */

if (!isset($data["json"]["files"])) {
    show_json_error("files not found");
}

$files = $data["json"]["files"];
foreach ($files as $key => $val) {
    if ($val["error"] != "") {
        continue;
    }
    $data = $val["data"];
    $val["data"] = "";
    $files[$key] = $val;
    $pre = "data:{$val["type"]};base64,";
    $len = strlen($pre);
    if (strncmp($pre, $data, $len) != 0) {
        continue;
    }
    $data = base64_decode(substr($data, $len));
    if (strlen($data) != $val["size"]) {
        continue;
    }
    $val["file"] = time() . "_" . get_unique_id_md5() . "_" . encode_bad_chars_file($val["name"]);
    $dir = get_directory("dirs/uploaddir", getcwd_protected() . "/data/upload");
    file_put_contents($dir . $val["file"], $data);
    $val["hash"] = md5($data);
    $files[$key] = $val;
}
output_handler_json($files);
