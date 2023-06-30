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
 * Deauthentication Token Action
 *
 * This file implements the logout action, allowing to deauthenticate users
 * using a valid token, for security reasons, the deauth action only can
 * be performed by the same actor that execute the login action
 *
 * The unique requirement to execute this action is to have a valid token
 */

crontab_users();

$token_id = current_token();
if (!$token_id) {
    show_json_error("deauthentication error");
}

$query = make_update_query("tbl_users_tokens", array(
    "active" => 0,
), make_where_query(array(
    "id" => $token_id,
)));
db_query($query);

output_handler_json(array(
    "status" => "ok",
));
