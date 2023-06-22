<?php

/**
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

/**
 * About this file
 *
 * This file implements the delete files action, requires a POST JSON request
 * with an array of files, and each array must contain the follow entries:
 * id, name, size, type, data, error, file, hash
 *
 * This action checks that not error is found, checks the file element, the
 * size of the file, the hash of the file, and then, remove the file and
 * clear the file and hash element of the array
 */

if (!isset($_DATA["json"]["files"])) {
    show_json_error("files not found");
}

$files = $_DATA["json"]["files"];
foreach ($files as $key => $val) {
    if ($val["error"] != "") {
        continue;
    }
    if (encode_bad_chars_file($val["file"]) != $val["file"]) {
        continue;
    }
    $dir = get_directory("dirs/uploaddir", getcwd_protected() . "/data/upload");
    if (filesize($dir . $val["file"]) != $val["size"]) {
        continue;
    }
    if (md5_file($dir . $val["file"]) != $val["hash"]) {
        continue;
    }
    unlink($dir . $val["file"]);
    $val["file"] = "";
    $val["hash"] = "";
    $files[$key] = $val;
}
output_handler_json($files);
