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
 * About this file
 *
 * This file implements the login action, allowing to authenticate users
 * using the pair of login/password to validate the credentials and get
 * a valid token to operate in SaltOS
 */

// This part disable users that have been expired
$query = make_update_query("tbl_users_passwords", array(
    "active" => 0,
), make_where_query(array(
    "active" => 1,
    "expires<=" => current_datetime(),
)));
db_query($query);

// Check parameters
foreach (array("user","pass") as $key) {
    if (!isset($data["json"][$key]) || $data["json"][$key] == "") {
        show_json_error("$key not found or void");
    }
}
$user = $data["json"]["user"];
$pass = $data["json"]["pass"];

// First check
$query = "SELECT * FROM tbl_users WHERE " . make_where_query(array(
    "active" => 1,
    "login" => $user,
));
$result = db_query($query);
$num_rows = db_num_rows($result);
$row = db_fetch_row($result);
db_free($result);
if ($num_rows != 1 || $user != $row["login"]) {
    show_json_error("authentication error");
}

// Second check
$query = "SELECT * FROM tbl_users_passwords WHERE " . make_where_query(array(
    "user_id" => $row["id"],
    "active" => 1,
    "expires>" => current_datetime(),
));
$result = db_query($query);
$num_rows = db_num_rows($result);
$row2 = db_fetch_row($result);
db_free($result);
if ($num_rows != 1) {
    show_json_error("authentication error");
} elseif (password_verify($pass, $row2["password"])) {
    // Nothing to do, password is correct!!!
} elseif (in_array($row2["password"], array(md5($pass),sha1($pass)))) {
    // Convert from MD5/SHA1 to password_hash format
    $row2["password"] = password_hash($pass, PASSWORD_DEFAULT);
    $query = make_update_query("tbl_users_passwords", array(
        "password" => $row2["password"]
    ), make_where_query(array(
        "id" => $row2["id"]
    )));
    db_query($query);
} else {
    show_json_error("authentication error");
}

// Continue
$query = make_update_query("tbl_users_logins", array(
    "active" => 0,
), make_where_query(array(
    "user_id" => $row["id"],
    "active" => 1,
)));
db_query($query);

$datetime = current_datetime();
$token = implode("-", array(
    bin2hex(random_bytes(4)),
    bin2hex(random_bytes(2)),
    bin2hex(random_bytes(2)),
    bin2hex(random_bytes(2)),
    bin2hex(random_bytes(6))
));
$expires = current_datetime(get_config("auth/tokenexpires"));
$renewals = get_config("auth/tokenrenewals");

$query = make_insert_query("tbl_users_logins", array(
    "user_id" => $row["id"],
    "active" => 1,
    "datetime" => $datetime,
    "remote_addr" => get_server("REMOTE_ADDR"),
    "user_agent" => get_server("HTTP_USER_AGENT"),
    "token" => $token,
    "expires" => $expires,
));
db_query($query);

output_handler_json(array(
    "status" => "ok",
    "token" => $token,
    "created_at" => $datetime,
    "expires_at" => $expires,
    "pending_renewals" => $renewals,
));
