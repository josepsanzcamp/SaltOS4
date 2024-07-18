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

// phpcs:disable Generic.Files.LineLength
// phpcs:disable PSR1.Files.SideEffects

/**
 * Email account library
 *
 * This library provides the necesary functions to manage accounts emails.
 */

/**
 * User is admin
 *
 * This function returns true if the current user has all perms for the app
 */
function __user_is_admin($app)
{
    $query = "SELECT * FROM tbl_perms WHERE active = 1";
    $rows = execute_query_array($query);
    foreach ($rows as $row) {
        if ($row["owner"] != "") {
            $perm = $row["code"] . "|" . $row["owner"];
        } else {
            $perm = $row["code"];
        }
        if (!check_user($app, $perm)) {
            return false;
        }
    }
    return true;
}
