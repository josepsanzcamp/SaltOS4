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
 * This file implements the app action, requires a GET REST request
 * and the order of the elements are:
 *
 * @[1] => the app that you want to execute
 * @[2] => the subapp that tou want to use, if the app only contains
 *         one subapp, this parameter is not necesary
 * @[3] => the id used in some subapps, for example, to get the data
 *         of specific customer using the id
 */

if (!isset($data["rest"][1])) {
    show_json_error("app not found");
}

$data["rest"][1] = encode_bad_chars($data["rest"][1]);
$file = "apps/" . $data["rest"][1] . "/app.xml";
if (!file_exists($file)) {
    show_json_error("app " . $data["rest"][1] . " not found");
}

$array = xmlfile2array($file);

if (!isset($data["rest"][2]) && count($array) == 1) {
    $data["rest"][2] = key($array);
}

if (!isset($data["rest"][2])) {
    show_json_error("subapp not found");
}

$data["rest"][2] = encode_bad_chars($data["rest"][2]);
if (!isset($array[$data["rest"][2]])) {
    show_json_error("subapp " . $data["rest"][2] . " not found");
}

if (!isset($data["rest"][3])) {
    $data["rest"][3] = 0;
}
$data["rest"][3] = intval($data["rest"][3]);

$array = eval_attr($array[$data["rest"][2]]);
output_handler_json($array);
