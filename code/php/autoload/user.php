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
 * Current Token
 *
 * This function returns the id of the current token, this info is retrieved
 * using the token of the request, for security reasons, this validation only
 * can be performed by the same origin that execute the login action
 */
function current_token()
{
    crontab_users();
    $token_id = execute_query("SELECT id FROM tbl_users_tokens WHERE " . make_where_query(array(
        "token" => get_server("HTTP_TOKEN"),
        "active" => 1,
        "remote_addr" => get_server("REMOTE_ADDR"),
        "user_agent" => get_server("HTTP_USER_AGENT"),
    )));
    return intval($token_id);
}

/**
 * Current User
 *
 * This function returns the id of the current user, this info is retrieved
 * using the token of the request
 */
function current_user()
{
    $user_id = execute_query("SELECT user_id FROM tbl_users_tokens WHERE " . make_where_query(array(
        "id" => current_token(),
        "active" => 1,
    )));
    return intval($user_id);
}

/**
 * Current Group
 *
 * This function returns the id of the current group, this info is retrieved
 * using the token of the request
 */
function current_group()
{
    $group_id = execute_query("SELECT group_id FROM tbl_users WHERE " . make_where_query(array(
        "user_id" => current_user(),
        "active" => 1,
    )));
    return intval($group_id);
}

/**
 * Crontab Users
 *
 * This function executes the maintenance queries to update the active field
 * in the passwords and tokens tables, it's intended to be used as helper
 *
 * Notes:
 *
 * This function uses an internal static variable to detect repeated executions
 * and only accepts the first execution, this is to prevent that multiples calls
 * to other actions and functions that requires the integrity of the passwords
 * and tokens
 */
function crontab_users()
{
    static $i_am_executed = false;
    if ($i_am_executed) {
        return;
    }
    $datetime = current_datetime();
    $time = current_time();
    $dow = current_dow();
    // Disable tokens that have been expired by the password expires
    $query = "UPDATE tbl_users_tokens SET active = 0 WHERE active = 1 AND user_id IN (
        SELECT user_id FROM tbl_users_passwords WHERE active = 1 AND expires <= '$datetime')";
    db_query($query);
    // Disable tokens that have been expired by the user time and day filter
    $query = "UPDATE tbl_users_tokens SET active = 0 WHERE active = 1 AND user_id IN (
        SELECT id FROM tbl_users WHERE (
            start = end OR
            (start < end AND ('$time' < start OR '$time' > end)) OR
            (start > end AND '$time' < start AND '$time' > end) OR
            substr(days, $dow, 1) = '0'))";
    db_query($query);
    // Disable passwords that have been expired
    $query = "UPDATE tbl_users_passwords SET active = 0 WHERE active = 1 AND expires <= '$datetime'";
    db_query($query);
    // Disable tokens that have been expired
    $query = "UPDATE tbl_users_tokens SET active = 0 WHERE active = 1 AND expires <= '$datetime'";
    db_query($query);
    // mark as executed
    $i_am_executed = true;
}
