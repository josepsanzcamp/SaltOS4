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
 * List action
 *
 * This action tries to facility the creation of lists with the tipicals
 * features suck as rows, actions for each row, and other improvements as
 * the list with count and without count.
 *
 * TODO: pending to add the order by from the list header
 */

// With this code, we allow rest and json at the same time
if (get_data("rest/1") != "" && get_data("json/app") == "") {
    set_data("json/app", get_data("rest/1"));
}

if (get_data("rest/2") != "" && get_data("json/subapp") == "") {
    set_data("json/subapp", get_data("rest/2"));
}

if (get_data("rest/3") != "" && get_data("json/id") == "") {
    set_data("json/id", get_data("rest/3"));
}

// Check for json/app, that is the name of the app to load
if (get_data("json/app") == "") {
    show_json_error("app not found");
}

set_data("json/app", encode_bad_chars(get_data("json/app")));
$file = "apps/" . get_data("json/app") . "/xml/app.xml";
if (!file_exists($file)) {
    show_json_error("app " . get_data("json/app") . " not found");
}

// Load the app xml file
$array = xmlfile2array($file);

if (!is_array($array) || !count($array)) {
    show_json_error("internal error");
}

// Check for json/subapp, that is the name of the subapp to load
if (get_data("json/subapp") == "") {
    set_data("json/subapp", key($array));
}

set_data("json/subapp", encode_bad_chars(get_data("json/subapp")));
if (!isset($array[get_data("json/subapp")])) {
    show_json_error("subapp " . get_data("json/subapp") . " not found");
}

// Check permissions
if (!check_user(get_data("json/app"), "list")) {
    show_json_error("Permission denied");
}

// Trick to allow requests like widget/table2
foreach ($array as $key => $val) {
    if (isset($val["#attr"]["id"])) {
        if (fix_key($key) == get_data("json/subapp") && $val["#attr"]["id"] == get_data("json/id")) {
            set_data("json/subapp", $key);
            unset($array[$key]["#attr"]["id"]);
        }
    }
}

// Get only the subapp part
$array = $array[get_data("json/subapp")];

// This line is a trick to allow attr in the subapp
$array = join4array($array);

// Check json arguments
if (!get_data("json/search")) {
    set_data("json/search", "");
}
if (!get_data("json/page")) {
    set_data("json/page", 0);
}

// Check xml arguments
if (!isset($array["order"])) {
    $array["order"] = "id DESC";
}
set_data("json/order", $array["order"]);
unset($array["order"]);
if (!isset($array["limit"])) {
    $array["limit"] = 15;
}
set_data("json/limit", $array["limit"]);
unset($array["limit"]);

// Compute offset using page and limit
set_data("json/offset", intval(get_data("json/page") * get_data("json/limit")));

// Check to remove header and footer to improve the performance
if (get_data("json/page")) {
    unset($array["footer"]);
}

// Eval the queries
$array = eval_attr($array);

// Prepare data and actions
if (!isset($array["data"])) {
    $array["data"] = [];
}
if (!isset($array["actions"])) {
    $array["actions"] = [];
}

// Add the actions to each row checking each permissions's row
foreach ($array["data"] as $key => $row) {
    $actions = [];
    foreach ($array["actions"] as $action) {
        $action = join4array($action);
        $table = app2table($action["app"]);
        $id = $row["id"];
        $sql = check_sql($action["app"], strtok($action["action"], "/"));
        $query = "SELECT id FROM $table WHERE id=" . strtok(strval($id), "/") . " AND $sql";
        $has_perm = execute_query($query);
        if ($has_perm) {
            $action["url"] = "app/{$action["app"]}/{$action["action"]}/{$row["id"]}";
        } else {
            $action["url"] = "";
        }
        $actions[] = $action;
    }
    if (count($actions)) {
        $array["data"][$key]["actions"] = $actions;
    }
}
unset($array["actions"]);

// If contains header and footer, unify to allow attr in the spec
if (isset($array["header"]) && is_array($array["header"])) {
    foreach ($array["header"] as $key => $val) {
        $array["header"][$key] = join4array($val);
    }
}
if (isset($array["footer"]) && is_array($array["footer"])) {
    foreach ($array["footer"] as $key => $val) {
        $array["footer"][$key] = join4array($val);
    }
}

// The end
output_handler_json($array);
