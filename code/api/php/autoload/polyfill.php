<?php

/**
 *  ____        _ _    ___  ____  _  _
 * / ___|  __ _| | |_ / _ \/ ___|| || |
 * \___ \ / _` | | __| | | \___ \| || |_
 *  ___) | (_| | | |_| |_| |___) |__   _|
 * |____/ \__,_|_|\__|\___/|____/   |_|
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
 */

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects

/**
 * Compatibility Layer / Polyfill Module
 *
 * This file provides fallback implementations for native PHP functions and
 * extension-specific logic that may be missing in older PHP versions or
 * specific operating system environments supported by SaltOS.
 *
 * Features:
 * - Backports for PHP 7.3+ array functions (array_key_first, array_key_last).
 * - Compatibility shims for POSIX functions on non-Unix (Windows) systems.
 *
 * Layout: the actual polyfill logic lives in the __*_helper() functions
 * below (never guarded: they can't collide with anything native PHP or an
 * extension provides). Each polyfilled function is just a thin delegate to
 * its same-named helper, grouped at the end of this file and guarded with
 * function_exists() so it's only defined when the native function/extension
 * is missing. That split exists so utest/test_polyfill.php can call the
 * helper directly and exercise this polyfill's actual code even on
 * machines where the native function already exists (PHP 7.3+ for
 * array_key_first/array_key_last, any POSIX system for posix_getuid),
 * where the plain names are already taken and can't be used to reach this
 * file's code.
 */

/**
 * Array Key Last Helper
 *
 * This function appear in PHP 7.3, and for previous version SaltOS
 * uses this code
 *
 * @array => the array where you want to obtain the last key
 *
 * Notes:
 *
 * Code copied from the follow web:
 * https://www.php.net/manual/es/function.array-key-last.php#124007
 */
function __array_key_last_helper(array $array)
{
    if (!empty($array)) {
        return key(array_slice($array, -1, 1, true));
    }
}

/**
 * Array Key First Helper
 *
 * This function appear in PHP 7.3, and for previous version SaltOS
 * uses this code
 *
 * @array => the array where you want to obtain the first key
 *
 * Notes:
 *
 * Code copied from the follow web:
 * https://www.php.net/manual/es/function.array-key-last.php#124007
 */
function __array_key_first_helper(array $arr)
{
    foreach ($arr as $key => $val) {
        return $key;
    }
}

/**
 * Posix GetUID Helper
 *
 * This function only is available in posix compilant systems like unix,
 * linux and mac, and for windows this is a short circuit
 */
function __posix_getuid_helper()
{
    return getmyuid();
}

/**
 * Thin delegates: only defined when the native function/extension is
 * missing, in which case each one just forwards to its same-named
 * __*_helper() above.
 */

if (!function_exists('array_key_last')) {
    /**
     * Array Key Last
     */
    function array_key_last(array $array)
    {
        return __array_key_last_helper($array);
    }
}

if (!function_exists('array_key_first')) {
    /**
     * Array Key First
     */
    function array_key_first(array $arr)
    {
        return __array_key_first_helper($arr);
    }
}

if (!function_exists('posix_getuid')) {
    /**
     * Posix GetUID
     */
    function posix_getuid()
    {
        return __posix_getuid_helper();
    }
}
