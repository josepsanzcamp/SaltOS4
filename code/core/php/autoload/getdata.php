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
 * Get data
 *
 * This function is intended to be used to retrieve values from the
 * data system
 *
 * @key     => the key that you want to retrieve the value
 * @default => the default value used when the key is not found
 */
function get_data($key, $default = "")
{
    global $_DATA;
    $keys = explode("/", $key);
    $count = count($keys);
    if ($count == 1) {
        if (isset($_DATA[$keys[0]])) {
            return $_DATA[$keys[0]];
        } else {
            return $default;
        }
    }
    if ($count == 2) {
        if (isset($_DATA[$keys[0]][$keys[1]])) {
            return $_DATA[$keys[0]][$keys[1]];
        } else {
            return $default;
        }
    }
    show_php_error(["phperror" => "key $key not found in " . __FUNCTION__]);
}

/**
 * Set data
 *
 * This function sets a value in the data system for the specified key
 *
 * @key => the key that you want to set
 * @val => the value that you want to set
 */
function set_data($key, $val)
{
    global $_DATA;
    $keys = explode("/", $key);
    $count = count($keys);
    if ($count == 1) {
        $_DATA[$keys[0]] = $val;
        return;
    }
    if ($count == 2) {
        $_DATA[$keys[0]][$keys[1]] = $val;
        return;
    }
    show_php_error(["phperror" => "key $key not found " . __FUNCTION__]);
}
