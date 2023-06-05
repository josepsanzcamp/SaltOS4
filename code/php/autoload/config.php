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
 * Get config
 *
 * This function is intended to be used to retrieve values from the
 * config system, as first level, the function try to get the value from
 * the tbl_config, and if it is not found, then the function try to get
 * the values from the config file.
 *
 * @key => the key that you want to retrieve the value
 */
function get_config($key)
{
    $row = array();
    $query = "SELECT val FROM tbl_config WHERE _key='{$key}'";
    if (db_check($query)) {
        $config = execute_query($query);
    } else {
        $config = null;
    }
    if ($config !== null) {
        $row = array($key => $config);
    } else {
        $row = get_default("configs");
    }
    if (!isset($row[$key])) {
        return null;
    }
    return $row[$key];
}

/*
 * Set config
 *
 * This function sets a value to a config key, the data will be
 * stored in the database using the tbl_config
 *
 * @key => the key that you want to set
 * @val => the value that you want to set
 */
function set_config($key, $val)
{
    $query = "SELECT val FROM tbl_config WHERE _key='{$key}'";
    $config = execute_query($query);
    if ($config === null) {
        $query = make_insert_query("tbl_config", array(
            "_key" => $key,
            "val" => $val
        ));
        db_query($query);
    } else {
        $query = make_update_query("tbl_config", array(
            "val" => $val
        ), make_where_query(array(
            "_key" => $key
        )));
        db_query($query);
    }
}

/*
 * Get default
 *
 * This function retrieve data from the config file
 *
 * @key => the key that you want to retrieve the value
 * @default => the default value used when the key is not found
 */
function get_default($key, $default = "")
{
    global $_CONFIG;
    $key = explode("/", $key);
    $count = count($key);
    $config = $_CONFIG;
    while ($count) {
        $key2 = array_shift($key);
        if (!isset($config[$key2])) {
            return $default;
        }
        $config = $config[$key2];
        $count--;
    }
    if ($config === "") {
        return $default;
    }
    return $config;
}
