<?php

/**
 *  ____        _ _    ___  ____    _  _    _
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / |
 * \___ \ / _` | | __| | | \___ \  | || |_ | |
 *  ___) | (_| | | |_| |_| |___) | |__   _|| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)_|
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
 */

declare(strict_types=1);

/**
 * Get data helper module
 *
 * This fie contains useful functions related to the $_DATA global variable, allow to get and set
 * values in the global $_DATA variable using xpath as key
 */

/**
 * Get data
 *
 * This function is intended to be used to retrieve values from the
 * data system
 *
 * @key => the key that you want to retrieve the value
 *
 * Notes:
 *
 * If you request the key rest/-1, then the function returns the last
 * value of the rest, intended to get the last value of the rest array,
 * too you can get the rest array using negative indexes beginning
 * from the last position
 */
function get_data($key)
{
    global $_DATA;
    $keys = explode('/', $key);
    $count = count($keys);
    // Special case for negative positions of rest
    if ($count === 2 && $keys[0] === 'rest' && intval($keys[1]) < 0) {
        $keys[1] = count($_DATA[$keys[0]]) + intval($keys[1]);
    }
    // Continue
    if ($count === 1) {
        return $_DATA[$keys[0]] ?? null;
    }
    if ($count === 2) {
        return $_DATA[$keys[0]][$keys[1]] ?? null;
    }
    if ($count === 3) {
        return $_DATA[$keys[0]][$keys[1]][$keys[2]] ?? null;
    }
    show_php_error(['phperror' => "key $key not found"]);
}

/**
 * Set data
 *
 * This function sets a value in the data system for the specified key
 *
 * @key => the key that you want to set
 * @val => the value that you want to set
 *
 * Notes:
 *
 * If null val is passed as argument, then the entry of the data is removed,
 * the main idea is to use the same method used by the setcookie that allow
 * to remove entries by setting the value to null
 */
function set_data($key, $val)
{
    global $_DATA;
    $keys = explode('/', $key);
    $count = count($keys);
    if ($count === 1) {
        if ($val !== null) {
            $_DATA[$keys[0]] = $val;
        } else {
            unset($_DATA[$keys[0]]);
        }
        return;
    }
    if ($count === 2) {
        if ($val !== null) {
            $_DATA[$keys[0]][$keys[1]] = $val;
        } else {
            unset($_DATA[$keys[0]][$keys[1]]);
        }
        return;
    }
    if ($count === 3) {
        if ($val !== null) {
            $_DATA[$keys[0]][$keys[1]][$keys[2]] = $val;
        } else {
            unset($_DATA[$keys[0]][$keys[1]][$keys[2]]);
        }
        return;
    }
    show_php_error(['phperror' => "key $key not found"]);
}
