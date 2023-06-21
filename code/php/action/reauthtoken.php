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
 * This file implements the renew action, allowing to renew tokens before
 * expire, for security reasons, the deauth action only can be performed by
 * the same actor that execute the login action
 */

$token_id = current_token();
if (!$token_id) {
    show_json_error("reauthentication error");
}

$query = "SELECT * FROM tbl_users_logins WHERE id='$token_id'";
$row = execute_query($query);

$renewals = get_config("auth/tokenrenewals");
if ($row["renewal_count"] >= $renewals) {
    show_json_error("reauthentication error");
}

$query = make_update_query("tbl_users_logins", array(
    "active" => 0,
), make_where_query(array(
    "id" => $token_id,
)));
db_query($query);

$token = implode("-", array(
    bin2hex(random_bytes(4)),
    bin2hex(random_bytes(2)),
    bin2hex(random_bytes(2)),
    bin2hex(random_bytes(2)),
    bin2hex(random_bytes(6))
));
$expires = current_datetime(get_config("auth/tokenexpires"));
$datetime = current_datetime();

$query = make_insert_query("tbl_users_logins", array(
    "user_id" => $row["user_id"],
    "active" => 1,
    "datetime" => $row["datetime"],
    "remote_addr" => $row["remote_addr"],
    "user_agent" => $row["user_agent"],
    "token" => $token,
    "expires" => $expires,
    "renewal_datetime" => $datetime,
    "renewal_count" => $row["renewal_count"] + 1,
));
db_query($query);

output_handler_json(array(
    "status" => "ok",
    "token" => $token,
    "created_at" => $row["datetime"],
    "expires_at" => $expires,
    "renewal_at" => $datetime,
    "pending_renewals" => $renewals - $row["renewal_count"] - 1,
));
