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
 * Delete action
 *
 * This action allow to delete registers in the database associated to
 * each app
 *
 * TODO
 */

$user_id = current_user();
if (!$user_id) {
    show_json_error("Permission denied");
}

$app = get_data("json/app");
$id = intval(get_data("json/id"));

if (!check_user($app, "delete")) {
    show_json_error("Permission denied");
}

$table = app2table($app);
$sql = check_sql($app, "delete");
$query = "SELECT id FROM $table WHERE id = $id AND $sql";
$exists = execute_query($query);
if (!$exists) {
    show_json_error("Permission denied");
}

// Prepare main query
$query = "DELETE FROM $table WHERE id = $id";
db_query($query);

// Prepare all subqueries
$subtables = app2subtables($app);
foreach ($subtables as $temp) {
    $subtable = $temp["subtable"];
    $field = $temp["field"];
    $query = "DELETE FROM $subtable WHERE $field = $id";
    db_query($query);
}

make_index($app, $id);
make_control($app, $id);
add_version($app, $id);

output_handler_json([
    "status" => "ok",
]);
