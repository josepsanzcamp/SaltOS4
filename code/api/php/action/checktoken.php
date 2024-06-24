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
 * Check token action
 *
 * This file implements the check action, allowing to check token's validity, the check
 * action only can be performed by the same actor that execute the login action
 *
 * The unique requirement to execute this action is to have a token to be checked, as the
 * result of this action is a flag that indicates the validity of the token, this action
 * returns a json with the status of te token instead of returns a json with an error in
 * case of non validity
 */

if (!semaphore_acquire("token")) {
    show_php_error(["phperror" => "Could not acquire the semaphore"]);
}

db_connect();
crontab_users();

$token_id = current_token();
if (!$token_id) {
    semaphore_release("token");
    output_handler_json([
        "status" => "ko",
        "reason" => "Permission denied",
        "code" => __get_code_from_trace(),
    ]);
}

$query = "SELECT * FROM tbl_users_tokens WHERE id='$token_id'";
$row = execute_query($query);

$updated_at = current_datetime();
$short_expires = current_datetime(get_config("auth/tokenshortexpires"));
$long_expires = date("Y-m-d H:i:s", strtotime($row["created_at"]) + get_config("auth/tokenlongexpires"));

$query = make_update_query("tbl_users_tokens", [
    "updated_at" => $updated_at,
    "expires_at" => min($short_expires, $long_expires),
], make_where_query([
    "id" => $token_id,
]));
db_query($query);

semaphore_release("token");
output_handler_json([
    "status" => "ok",
    "token" => $row["token"],
    "created_at" => $row["created_at"],
    "updated_at" => $updated_at,
    "expires_at" => min($short_expires, $long_expires),
]);
