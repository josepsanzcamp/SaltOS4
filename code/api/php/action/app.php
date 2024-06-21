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
 * Application action
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

// Check for rest/1, that is the name of the app to load
if (get_data("rest/1") == "") {
    show_json_error("app not found");
}

set_data("rest/1", encode_bad_chars(get_data("rest/1")));
$file = "apps/" . get_data("rest/1") . "/xml/app.xml";
if (!file_exists($file)) {
    show_json_error("app " . get_data("rest/1") . " not found");
}

// Load the app xml file
$array = xmlfile2array($file);

if (!is_array($array) || !count($array)) {
    show_json_error("internal error");
}

// Check for rest/2, that is the name of the subapp to load
if (get_data("rest/2") == "" && count($array) == 1) {
    set_data("rest/2", key($array));
}

if (get_data("rest/2") == "") {
    foreach ($array as $key => $val) {
        if (isset($val["#attr"]["default"]) && eval_bool($val["#attr"]["default"])) {
            set_data("rest/2", $key);
        }
    }
}

if (get_data("rest/2") == "") {
    show_json_error("subapp not found");
}

set_data("rest/2", encode_bad_chars(get_data("rest/2")));
if (!isset($array[get_data("rest/2")])) {
    show_json_error("subapp " . get_data("rest/2") . " not found");
}

// Connect to the database
db_connect();

// Check permissions
if (!check_app_perm_id(get_data("rest/1"), get_data("rest/2"))) {
    show_json_error("Permission denied");
}

// Trick to allow requests like widget/table2
foreach ($array as $key => $val) {
    if (isset($val["#attr"]["id"])) {
        if (fix_key($key) == get_data("rest/2") && $val["#attr"]["id"] == get_data("rest/3")) {
            set_data("rest/2", $key);
        }
    }
}

// Get only the subapp part
$array = $array[get_data("rest/2")];
set_data("rest/2", fix_key(get_data("rest/2")));

// This line is a trick to allow attr in the subapp
$array = join_attr_value($array);

// Eval the app and returns the result
$array = eval_attr($array);
$array = __apply_locale($array);
output_handler_json($array);
