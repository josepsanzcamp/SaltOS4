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
 * Cron utils helper module
 *
 * This fie contains useful functions related to cron operations
 */

/**
 * TODO
 *
 * TODO
 */
function __cron_compare($val, $now)
{
    // true case
    if ($val == '*') {
        return true;
    }
    // list of options
    if (strpos($val, ',') !== false) {
        $vals = explode(',', $val);
        foreach ($vals as $val) {
            if (__cron_compare($val, $now)) {
                return true;
            }
        }
        return false;
    }
    // using module
    if (strpos($val, '*/') !== false) {
        $module = intval(substr($val, 2)) ;
        return $now % $module == 0;
    }
    // using range
    if (strpos($val, '-') !== false) {
        $range = explode('-', $val, 2);
        return $now >= intval($range[0]) && $now <= intval($range[1]);
    }
    // direct case
    return intval($val) == $now;
}

/**
 * TODO
 *
 * TODO
 */
function __cron_is_now($minute, $hour, $day, $month, $dow)
{
    $now = getdate();
    return __cron_compare($minute, $now['minutes']) &&
           __cron_compare($hour, $now['hours']) &&
           __cron_compare($day, $now['mday']) &&
           __cron_compare($month, $now['mon']) &&
           __cron_compare($dow, $now['wday']);
}

/**
 * TODO
 *
 * TODO
 */
function __cron_users($arg)
{
    // all users
    if ($arg == '*') {
        return execute_query_array('SELECT login FROM tbl_users WHERE active = 1');
    }
    // list of users
    if (strpos($arg, ',') !== false) {
        return explode(',', $arg);
    }
    // default case
    return [$arg];
}
