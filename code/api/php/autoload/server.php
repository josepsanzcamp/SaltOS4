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
 * Server helper module
 *
 * This fie contains useful functions related to the $_SERVER global variable, currently only publish
 * a getter function, but in the future, can store more features if it is needed
 */

/**
 * Get Server
 *
 * This function returns the server variable requested by index if it exists
 *
 * @key => the index key used to get the value of the server
 */
function get_server($key)
{
    return $_SERVER[$key] ?? null;
}

/**
 * Set Server
 *
 * This function is intended to replace some server variabe in runtime mode
 *
 * @key => the index key used to get the value of the server
 * @val => the value that you want to set in the server array
 */
function set_server($key, $val)
{
    $_SERVER[$key] = $val;
}

/**
 * TODO
 *
 * TODO
 */
function current_hash()
{
    $hash = get_server("QUERY_STRING");
    if (substr($hash, 0, 1) == "/") {
        $hash = substr($hash, 1);
    }
    return $hash;
}
