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
 * Note: Each function is wrapped in a !function_exists() check to prevent
 * redeclaration conflicts with native implementations or other libraries.
 */

if (!function_exists('array_key_last')) {
    /**
     * Array Key Last
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
    function array_key_last(array $array)
    {
        if (!empty($array)) {
            return key(array_slice($array, -1, 1, true));
        }
    }
}

if (!function_exists('array_key_first')) {
    /**
     * Array Key First
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
    function array_key_first(array $arr)
    {
        foreach ($arr as $key => $val) {
            return $key;
        }
    }
}

if (!function_exists('posix_getuid')) {
    /**
     * Posix GetUID
     *
     * This function only is available in posix compilant systems like unix,
     * linux and mac, and for windows this is a short circuit
     */
    function posix_getuid()
    {
        return getmyuid();
    }
}
