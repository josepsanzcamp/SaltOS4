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
 * Authentication token action
 *
 * This file implements the login action, allowing to authenticate users using the pair
 * of login/password to validate the credentials and get a valid token to operate in SaltOS
 *
 * @user => username used in the authentication process
 * @pass => password used in the authentication process
 *
 * This action not requires a valid token, all valid tokens associated
 * to the user will be revoked when a new token is assigned, as the result of this action
 * is a flag that indicates the validity of the token, this action returns a json with the
 * status of te token instead of returns a json with an error in case of non validity
 */

if (!semaphore_acquire("token")) {
    show_php_error(["phperror" => "Could not acquire the semaphore"]);
}

crontab_users();

// Check parameters
foreach (["user", "pass"] as $key) {
    if (get_data("json/$key") == "") {
        semaphore_release("token");
        show_json_error("$key not found or void");
    }
}
$user = get_data("json/user");
$pass = get_data("json/pass");

// First check
$query = "SELECT * FROM tbl_users WHERE " . make_where_query([
    "active" => 1,
    "login" => $user,
]);
$row = execute_query($query);
if (!is_array($row) || !isset($row["login"]) || $user != $row["login"]) {
    semaphore_release("token");
    output_handler_json([
        "status" => "ko",
    ]);
}

// Second check
$query = "SELECT * FROM tbl_users_passwords WHERE " . make_where_query([
    "user_id" => $row["id"],
    "active" => 1,
]);
$row2 = execute_query($query);
if (!is_array($row2) || !isset($row2["password"])) {
    semaphore_release("token");
    output_handler_json([
        "status" => "ko",
    ]);
} elseif (password_verify($pass, $row2["password"])) {
    // Nothing to do, password is correct!!!
} elseif (in_array($row2["password"], [md5($pass), sha1($pass)])) {
    // Convert from MD5/SHA1 to password_hash format
    $row2["password"] = password_hash($pass, PASSWORD_DEFAULT);
    $query = make_update_query("tbl_users_passwords", [
        "password" => $row2["password"],
    ], make_where_query([
        "id" => $row2["id"],
    ]));
    db_query($query);
} else {
    semaphore_release("token");
    output_handler_json([
        "status" => "ko",
    ]);
}

// Continue
$query = make_update_query("tbl_users_tokens", [
    "active" => 0,
], make_where_query([
    "user_id" => $row["id"],
    "active" => 1,
]));
db_query($query);

$datetime = current_datetime();
$token = get_unique_token();
$expires = current_datetime(get_config("auth/tokenexpires"));
$renewals = get_config("auth/tokenrenewals");

$query = make_insert_query("tbl_users_tokens", [
    "user_id" => $row["id"],
    "active" => 1,
    "datetime" => $datetime,
    "remote_addr" => get_server("REMOTE_ADDR"),
    "user_agent" => get_server("HTTP_USER_AGENT"),
    "token" => $token,
    "expires" => $expires,
]);
db_query($query);

semaphore_release("token");
output_handler_json([
    "status" => "ok",
    "token" => $token,
    "created_at" => $datetime,
    "expires_at" => $expires,
    "pending_renewals" => $renewals,
]);
