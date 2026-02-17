<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 *
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
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
    if ($days === null) {
        return $days;
    }
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
