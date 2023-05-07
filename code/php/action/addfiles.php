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

if (isset($data["input"]["files"])) {
    $files = $data["input"]["files"];
    foreach ($files as $key => $val) {
        if ($val["error"] != "") {
            continue;
        }
        $data = $val["data"];
        unset($val["data"]);
        $pre = "data:{$val["type"]};base64,";
        $len = strlen($pre);
        if (strncmp($pre, $data, $len) != 0) {
            continue;
        }
        $data = base64_decode(substr($data, $len));
        $val["file"] = time() . "_" . get_unique_id_md5() . "_" . encode_bad_chars_file($val["name"]);
        $dir = get_directory("dirs/uploaddir", getcwd_protected() . "/data/upload");
        file_put_contents($dir . $val["file"], $data);
        $val["hash"] = md5($data);
        $files[$key] = $val;
    }
    addlog(sprintr($files),"files.log");
}
die();
