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
 * Config helper module
 *
 * This fie contains useful functions related to configuration features
 */

/**
 * Get config
 *
 * This function is intended to be used to retrieve values from the
 * config system, as first level, the function try to get the value from
 * the tbl_config, and if it is not found, then the function try to get
 * the values from the config file.
 *
 * @key     => the key that you want to retrieve the value
 * @user_id => the user_id used in the first search step
 *
 * Notes:
 *
 * This function is a new release of the olds getConfig and getDefault,
 * depending of the user_id argument, it tries to search in the config
 * file or in the database, for negative values the function uses the
 * config file and for zero or positive values, tries to search it in
 * the database
 */
function get_config($key, $user_id = -1)
{
    global $_CONFIG;
    if ($user_id < 0) {
        // Try to search the key in the config file
        $keys = explode("/", $key);
        $count = count($keys);
        if ($count == 1) {
            return $_CONFIG[$keys[0]] ?? null;
        }
        if ($count == 2) {
            return $_CONFIG[$keys[0]][$keys[1]] ?? null;
        }
        show_php_error(["phperror" => "key $key not found"]);
    }
    // Search the key for the specified user in the database
    $query = "SELECT val FROM tbl_config WHERE " . make_where_query([
        "user_id" => $user_id,
        "key" => $key,
    ]);
    if (db_check($query)) {
        $val = execute_query($query);
        return $val;
    }
    return null;
}

/**
 * Set config
 *
 * This function sets a value to a config key, the data will be stored in the
 * database using the tbl_config for zero or positive values of user_id, and
 * in the memory of the config file for negative user_id values
 *
 * @key     => the key that you want to set
 * @val     => the value that you want to set
 * @user_id => the user_id used as filter
 *
 * Notes:
 *
 * If null val is passed as argument, then the entry of the config or database
 * is removed, the main idea is to use the same method used by the setcookie that
 * allow to remove entries by setting the value to null
 */
function set_config($key, $val, $user_id = -1)
{
    global $_CONFIG;
    if ($user_id < 0) {
        // Try to sets the val for the specified key in the config file
        // This case only affects to the memory version of the config file
        $keys = explode("/", $key);
        $count = count($keys);
        if ($count == 1) {
            if ($val !== null) {
                $_CONFIG[$keys[0]] = $val;
            } else {
                unset($_CONFIG[$keys[0]]);
            }
            return;
        }
        if ($count == 2) {
            if ($val !== null) {
                $_CONFIG[$keys[0]][$keys[1]] = $val;
            } else {
                unset($_CONFIG[$keys[0]][$keys[1]]);
            }
            return;
        }
        show_php_error(["phperror" => "key $key not found"]);
    }
    // Try to insert or update the key for the specified user
    // In this case, zero user is allowed and used as global user
    $query = "SELECT id FROM tbl_config WHERE " . make_where_query([
        "user_id" => $user_id,
        "key" => $key,
    ]);
    $id = execute_query($query);
    if ($id === null) {
        if ($val !== null) {
            $query = make_insert_query("tbl_config", [
                "user_id" => $user_id,
                "key" => $key,
                "val" => $val,
            ]);
            db_query($query);
        }
    } else {
        if ($val !== null) {
            $query = make_update_query("tbl_config", [
                "val" => $val,
            ], make_where_query([
                "id" => $id,
            ]));
            db_query($query);
        } else {
            $query = "DELETE FROM tbl_config WHERE " . make_where_query([
                "id" => $id,
            ]);
            db_query($query);
        }
    }
}

/**
 * Detect config files
 *
 * This function returns the files found in the main path, in the apps path and in the files path
 *
 * @file => the pattern used to search files
 */
function detect_config_files($file)
{
    $files = array_merge(glob("data/files/" . basename($file)), glob($file), glob("apps/*/{$file}"));
    return $files;
}
