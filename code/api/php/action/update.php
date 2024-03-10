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
 * Update action
 *
 * This action allow to update registers in the database associated to
 * each app and requires the app, id, data and a valid token.
 *
 * TODO
 */

$user_id = current_user();
if (!$user_id) {
    show_json_error("Permission denied");
}

$app = get_data("rest/1");
$id = intval(get_data("rest/2"));
$data = get_data("json/data");

if (!check_app_perm_id($app, "edit", $id)) {
    show_json_error("Permission denied");
}

$table = app2table($app);
$fields = array_flip(array_column(get_fields_from_dbschema($table), "name"));
$subtables = array_flip(array_diff(array_column(app2subtables($app), "alias"), [""]));
$error = array_diff_key($data, $fields, $subtables);
if (count($error)) {
    show_json_error("Permission denied");
}

// Separate the data associated to a subtables
$subdata = array_intersect_key($data, $subtables);
$data = array_diff_key($data, $subdata);

// Prepare main query
if (count($data)) {
    $query = make_update_query($table, $data, "id = $id");
    db_query($query);
}

// Prepare all subqueries
$subtables = app2subtables($app);
foreach ($subtables as $temp) {
    $alias = $temp["alias"];
    $subtable = $temp["subtable"];
    $field = $temp["field"];
    if (isset($subdata[$alias])) {
        foreach ($subdata[$alias] as $temp2) {
            if (!isset($temp2["id"])) {
                // Insert new subdata
                $temp2[$field] = $id;
                $query = make_insert_query($subtable, $temp2);
                db_query($query);
            } elseif (intval($temp2["id"]) > 0) {
                // Update the subdata
                $id2 = intval($temp2["id"]);
                unset($temp2["id"]);
                $query = make_update_query($subtable, $temp2, "id = $id2 AND $field = $id");
                db_query($query);
            } elseif (intval($temp2["id"]) < 0) {
                // Delete the subdata
                $id2 = -intval($temp2["id"]);
                $query = "DELETE FROM $subtable WHERE id = $id2 AND $field = $id";
                db_query($query);
            } else {
                show_php_error(["phperror" => "subdata found with id=0"]);
            }
        }
    }
}

make_index($app, $id);
make_control($app, $id);
add_version($app, $id);

output_handler_json([
    "status" => "ok",
]);
