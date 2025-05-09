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
 * Config helper module
 *
 * This fie contains useful functions related to configuration features
 */

/**
 * Get config
 *
 * This function is intended to be used to retrieve values from the config
 * system, as first level, the function try to get the value from the
 * tbl_config, and if it is not found, then the function try to get the
 * values from the config file.
 *
 * @key     => the key that you want to retrieve the value
 * @user_id => the user_id used in the first search step
 *
 * Notes:
 *
 * This function is a new release of the olds getConfig and getDefault,
 * depending of the user_id argument, it tries to search in the config file or
 * in the database, for negative values the function uses the config file and
 * for zero or positive values, tries to search it in the database
 *
 * To prevent errors in case of duplicates, we are using the ORDER BY id ASC
 * LIMIT 1, this allow to get and set only the first register found by the
 * select query
 */
function get_config($key, $user_id = -1)
{
    global $_CONFIG;
    if ($user_id < 0) {
        // Try to search the key in the config file
        $keys = explode('/', $key);
        $count = count($keys);
        if ($count == 1) {
            return $_CONFIG[$keys[0]] ?? null;
        }
        if ($count == 2) {
            return $_CONFIG[$keys[0]][$keys[1]] ?? null;
        }
        show_php_error(['phperror' => "key $key not found"]);
    }
    // Search the key for the specified user in the database
    $query = 'SELECT val FROM tbl_config LIMIT 1';
    if (db_check($query)) {
        $query = 'SELECT val FROM tbl_config
            WHERE user_id = ? AND `key` = ? ORDER BY id ASC LIMIT 1';
        $val = execute_query($query, [$user_id, $key]);
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
 * is removed, the main idea is to use the same method used by the setcookie
 * that allow to remove entries by setting the value to null
 *
 * To prevent errors in case of duplicates, we are using the ORDER BY id ASC
 * LIMIT 1, this allow to get and set only the first register found by the
 * select query
 */
function set_config($key, $val, $user_id = -1)
{
    global $_CONFIG;
    if ($user_id < 0) {
        // Try to sets the val for the specified key in the config file
        // This case only affects to the memory version of the config file
        $keys = explode('/', $key);
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
        show_php_error(['phperror' => "key $key not found"]);
    }
    // Try to insert or update the key for the specified user
    // In this case, zero user is allowed and used as global user
    $query = 'SELECT id FROM tbl_config
        WHERE user_id = ? AND `key` = ? ORDER BY id ASC LIMIT 1';
    $id = execute_query($query, [$user_id, $key]);
    if ($id === null) {
        if ($val !== null) {
            $query = 'INSERT INTO tbl_config(user_id, `key`, val) VALUES (?, ?, ?)';
            db_query($query, [$user_id, $key, $val]);
        }
    } else {
        if ($val !== null) {
            $query = 'UPDATE tbl_config SET val = ? WHERE `id` = ?';
            db_query($query, [$val, $id]);
        } else {
            $query = 'DELETE FROM tbl_config WHERE id = ?';
            db_query($query, [$id]);
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
    $files = array_merge(glob($file), glob("apps/*/$file"), glob('data/files/' . basename($file)));
    return $files;
}

/**
 * Get config array
 *
 * This function is intended to retrieve the configuration associated to an user
 * using a prefix for all keys.
 *
 * @prefix  => the prefix used in the keys of the config
 * @user_id => the user_id used in the search query
 */
function get_config_array($prefix, $user_id)
{
    $query = 'SELECT `key`, val FROM tbl_config WHERE user_id = ? AND `key` LIKE ?';
    $rows = execute_query_array($query, [$user_id, $prefix . '%']);
    $array = array_column($rows, 'val', 'key');
    //~ $array = array_map(function ($val) {
        //~ return json_decode($val, true);
    //~ }, $array);
    return $array;
}

/**
 * Prepare Config Files
 *
 * This function tries to join all config files into one unique structure, to do it
 * joins nodes with the same key, for example, you can use the data/files/config.xml
 * file to set the specific database configuration and overwrite the db/pdo_mysql or
 * the db/type, the main idea is to replace the nodes of the second level because the
 * arrays2array function join previously the differents files into one structure usin
 * the first level to do the join, and only adds the repeated overload without take
 * decisions about overwrite the contents like this function do
 *
 * @array => the main array that you want to prepare as config array
 */
function prepare_config_files($array)
{
    foreach ($array as $key => $val) {
        foreach ($val as $key2 => $val2) {
            $key3 = fix_key($key2);
            if ($key2 != $key3) {
                unset($array[$key][$key2]);
                $array[$key][$key3] = $val2;
            }
        }
    }
    return $array;
}
