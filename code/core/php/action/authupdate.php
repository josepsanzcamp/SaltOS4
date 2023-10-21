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
 * Authentication update action
 *
 * This file implements the update password action, allowing to authenticated
 * users by a token, and providing the old password to update a new password
 *
 * @oldpass   => Old password, must to validate the active password of the user
 *               associated to the token used in the action
 * @newpass   => New password, must to be new, must to pass the score check and
 *               never must to be used in the system for the user
 * @renewpass => The repeated new password, to prevent writing errors
 *
 * This action requires a valid token associated to the user that wants to do
 * the password update
 */

if (!semaphore_acquire("token")) {
    show_php_error(["phperror" => "Could not acquire the semaphore"]);
}

crontab_users();

$user_id = current_user();
if (!$user_id) {
    semaphore_release("token");
    show_json_error("authentication update error");
}

// Check parameters
foreach (["oldpass", "newpass", "renewpass"] as $key) {
    if (get_data("json/$key") == "") {
        semaphore_release("token");
        show_json_error("$key not found or void");
    }
}
$oldpass = get_data("json/oldpass");
$newpass = get_data("json/newpass");
$renewpass = get_data("json/renewpass");

// Password checks
$query = "SELECT * FROM tbl_users_passwords WHERE " . make_where_query([
    "user_id" => $user_id,
    "active" => 1,
]);
$row = execute_query($query);
if (!is_array($row) || !isset($row["password"])) {
    semaphore_release("token");
    show_json_error("authentication update error");
}
if (!password_verify($oldpass, $row["password"])) {
    semaphore_release("token");
    show_json_error("old password authentication error");
}
if ($newpass != $renewpass) {
    semaphore_release("token");
    show_json_error("new password differs");
}

// Score check
$minscore = current_datetime(get_config("auth/passwordminscore"));
if (password_strength($newpass) < $minscore) {
    semaphore_release("token");
    show_json_error("new password strength error");
}

// Old passwords check
$query = "SELECT password FROM tbl_users_passwords WHERE " . make_where_query([
    "user_id" => $user_id,
]);
$oldspass = execute_query_array($query);
foreach ($oldspass as $oldpass) {
    if (password_verify($newpass, $oldpass)) {
        semaphore_release("token");
        show_json_error("new password used previously");
    }
}

// Continue
$query = make_update_query("tbl_users_passwords", [
    "active" => 0,
], make_where_query([
    "id" => $row["id"],
]));
db_query($query);

$newpass = password_hash($newpass, PASSWORD_DEFAULT);
$datetime = current_datetime();
$expires = current_datetime(get_config("auth/passwordexpires"));

$query = make_insert_query("tbl_users_passwords", [
    "active" => 1,
    "user_id" => $user_id,
    "datetime" => $datetime,
    "remote_addr" => get_server("REMOTE_ADDR"),
    "user_agent" => get_server("HTTP_USER_AGENT"),
    "password" => $newpass,
    "expires" => $expires,
]);
db_query($query);

semaphore_release("token");
output_handler_json([
    "status" => "ok",
    "updated_at" => $datetime,
    "expires_at" => $expires,
]);
