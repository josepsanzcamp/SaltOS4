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

if (!isset($data["rest"][1])) {
    show_json_error("file not found");
}

if (!isset($data["rest"][2])) {
    show_json_error("node not found");
}

$data["rest"][1] = encode_bad_chars($data["rest"][1]);
$file = "apps/" . $data["rest"][1] . "/app.xml";
if (!file_exists($file)) {
    show_json_error("file " . $data["rest"][1] . " not found");
}

$array = xml2array($file);

$data["rest"][2] = encode_bad_chars($data["rest"][2]);
if (!isset($array[$data["rest"][2]])) {
    show_json_error("node " . $data["rest"][2] . " not found");
}

$array = $array[$data["rest"][2]];
output_handler_json($array);
