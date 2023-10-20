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
 * Insert action
 *
 * This action allow to insert registers in the database associated to
 * each app
 *
 * TODO
 */

$user_id = current_user();
if (!$user_id) {
    show_json_error("authentication error");
}

$app = get_data("json/app");
$data = get_data("json/data");

if (!check_user($app, "create")) {
    show_json_error("permission denied");
}

$table = app2table($app);
$query = make_insert_query($table, $data);
db_query($query);

$id = execute_query("SELECT MAX(id) FROM $table");
make_index($app, $id);
make_control($app, $id);
add_version($app, $id);

output_handler_json([
    "status" => "ok",
]);
