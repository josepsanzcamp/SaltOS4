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

// phpcs:disable PSR1.Files.SideEffects

/**
 * Compatibility helper module
 *
 * This file add some functions used by SaltOS that can not be found in all allowed versions of PHP
 */

if (!function_exists("array_key_last")) {
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

if (!function_exists("array_key_first")) {
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
        foreach ($arr as $key => $unused) {
            return $key;
        }
    }
}
