<?php

/*
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

/*
 * Current User
 *
 * This function returns the id of the current user, this info is retrieved
 * using the token of the request
 */
function current_user()
{
    $token = get_server("HTTP_TOKEN");
    $user_id = execute_query("SELECT user_id FROM tbl_tokens WHERE token='$token'");
    return intval($user_id);
}

/*
 * Current Group
 *
 * This function returns the id of the current group, this info is retrieved
 * using the token of the request
 */
function current_group()
{
    $user_id = current_user();
    $group_id = execute_query("SELECT group_id FROM tbl_users WHERE user_id='$user_id'");
    return intval($group_id);
}
