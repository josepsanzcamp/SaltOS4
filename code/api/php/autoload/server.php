<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2025 by Josep Sanz Campderrós
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
    $keys = explode('/', $key);
    $count = count($keys);
    if ($count == 1) {
        return $_SERVER[$keys[0]] ?? null;
    }
    if ($count == 2) {
        return $_SERVER[$keys[0]][$keys[1]] ?? null;
    }
    show_php_error(['phperror' => "key $key not found"]);
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
    $keys = explode('/', $key);
    $count = count($keys);
    if ($count == 1) {
        if ($val !== null) {
            $_SERVER[$keys[0]] = $val;
        } else {
            unset($_SERVER[$keys[0]]);
        }
        return;
    }
    if ($count == 2) {
        if ($val !== null) {
            $_SERVER[$keys[0]][$keys[1]] = $val;
        } else {
            unset($_SERVER[$keys[0]][$keys[1]]);
        }
        return;
    }
    show_php_error(['phperror' => "key $key not found"]);
}

/**
 * Current hash
 *
 * This function tries to do the same like current_user but for the hash parameter
 * obtained from the QUERY_STRING server variable
 */
function current_hash()
{
    $hash = get_server('QUERY_STRING');
    if (is_string($hash) && substr($hash, 0, 1) == '/') {
        $hash = substr($hash, 1);
    }
    return $hash;
}

/**
 * Validates an IP address.
 *
 * This function checks whether the provided string is a valid IPv4 or IPv6 address.
 * If the IP address is valid, it returns it as-is; otherwise, it returns null.
 *
 * @ip => The IP address to validate.
 *
 * Return the valid IP address, or null if the input is not a valid IP address.
 */
function check_ip_addr($ip)
{
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return null;
    }
    return $ip;
}
