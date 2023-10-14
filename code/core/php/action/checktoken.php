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

crontab_users();

$token_id = current_token();
if (!$token_id) {
    output_handler_json([
        "status" => "ko",
    ]);
}

$query = "SELECT * FROM tbl_users_tokens WHERE id='$token_id'";
$row = execute_query($query);
$renewals = get_config("auth/tokenrenewals");

output_handler_json([
    "status" => "ok",
    "token" => $row["token"],
    "created_at" => $row["datetime"],
    "expires_at" => $row["expires"],
    "pending_renewals" => $renewals - $row["renewal_count"],
]);
