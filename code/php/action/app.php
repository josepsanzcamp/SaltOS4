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
 * Application Action
 *
 * This file implements the app action, requires a GET REST request
 * and the order of the elements are:
 *
 * @1 => the app that you want to execute
 * @2 => the subapp that tou want to use, if the app only contains
 *       one subapp, this parameter is not necesary
 * @3 => the id used in some subapps, for example, to get the data
 *       of specific customer using the id
 */

// Check for first argument, that is the name of the app to load
if (!isset($_DATA["rest"][1])) {
    show_json_error("app not found");
}

$_DATA["rest"][1] = encode_bad_chars($_DATA["rest"][1]);
$file = "apps/" . $_DATA["rest"][1] . "/app.xml";
if (!file_exists($file)) {
    show_json_error("app " . $_DATA["rest"][1] . " not found");
}

$array = xmlfile2array($file);

// Check for second argument, that is the subapp to load
if (!isset($_DATA["rest"][2]) && count($array) == 1) {
    $_DATA["rest"][2] = key($array);
}

if (!isset($_DATA["rest"][2])) {
    show_json_error("subapp not found");
}

$_DATA["rest"][2] = encode_bad_chars($_DATA["rest"][2]);
if (!isset($array[$_DATA["rest"][2]])) {
    show_json_error("subapp " . $_DATA["rest"][2] . " not found");
}

// Check permissions
if (!check_user($_DATA["rest"][1], $_DATA["rest"][2])) {
    show_json_error("Permission denied");
}

// Define the third argument used commonly by some apps
if (!isset($_DATA["rest"][3])) {
    $_DATA["rest"][3] = 0;
}
$_DATA["rest"][3] = intval($_DATA["rest"][3]);

// Trick to allow request as widget/2
$dict = array();
foreach ($array as $key => $val) {
    if (fix_key($key) == $_DATA["rest"][2] && isset($val["#attr"]["id"])) {
        $dict[$_DATA["rest"][2] . "/" . $val["#attr"]["id"]] = $key;
    }
}
if (count($dict) > 1) {
    if (!isset($dict[$_DATA["rest"][2] . "/" . $_DATA["rest"][3]])) {
        show_json_error("subsubapp not found");
    }
    $_DATA["rest"][2] = $dict[$_DATA["rest"][2] . "/" . $_DATA["rest"][3]];
}

// Eval the app and returns the result
$array = eval_attr($array[$_DATA["rest"][2]]);
output_handler_json($array);
