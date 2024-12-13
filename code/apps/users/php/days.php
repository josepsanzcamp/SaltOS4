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
 * Days functions
 *
 * This file contain all functions needed by the days feature
 */

/**
 * Days to bin
 *
 * This function tries to convert the days format used by the multiselect
 * to the string expected by the database formed by ones and zeroes to
 * represent if a day is operative for the user or not, for example, the
 * selection 64,32,16,8,4 is returned like from monday to friday (1111100)
 *
 * @days => the string containing the days in power of two separated by comma
 */
function days2bin($days)
{
    if ($days === null) {
        return $days;
    }
    $days = array_diff(explode(',', $days), ['']);
    $days = decbin(array_sum($days));
    $days = str_pad($days, 7, '0', STR_PAD_LEFT);
    return $days;
}

/**
 * Bin to days
 *
 * This function tries to do the reverse action that the previous function,
 * is able to get an string like 1111100 and returns the list of all bits in
 * decimal like 64,32,16,8,4.
 *
 * @days => the string containing the days in binary format
 */
function bin2days($days)
{
    $days = str_split($days);
    $days = array_reverse($days);
    foreach ($days as $key => $val) {
        $days[$key] = 2 ** $key * intval($val);
    }
    $days = array_diff($days, [0]);
    $days = implode(',', $days);
    return $days;
}

/**
 * Fix for days
 *
 * This function is intended to be used as wrapper in the result of the query
 * that contains an element called days, in the database the days is stored
 * using the binary notation like 1111100, and for the user interface, is needed
 * to translate this string into a decimal string like 64,32,16,8,4.
 *
 * @data => the data obtained from an execute_query, for example, they must contain
 *          an entry called days.
 */
function fix4days($data)
{
    $data['days'] = bin2days($data['days']);
    return $data;
}
