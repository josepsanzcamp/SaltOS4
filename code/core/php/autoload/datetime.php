<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2023 by Josep Sanz Campderrós
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
 * Current Date
 *
 * This function returns the current date in a YYYY-MM-DD format, this is used
 * by a lot of functions in SaltOS, allow to specify a bias used to move the
 * current time mark
 *
 * @offset => the bias added to the current time mark
 */
function current_date($offset = 0)
{
    return date("Y-m-d", time() + (int)$offset);
}

/**
 * Current Time
 *
 * This function returns the current time in a HH:II:SS format, this is used
 * by a lot of functions in SaltOS, allow to specify a bias used to move the
 * current time mark
 *
 * @offset => the bias added to the current time mark
 */
function current_time($offset = 0)
{
    return date("H:i:s", time() + (int)$offset);
}

/**
 * Current DateTime
 *
 * This function returns the current date and time in a YYYY-MM-SS HH:II:SS format,
 * this is used by a lot of functions in SaltOS, allow to specify a bias used to
 * move the current time mark
 *
 * @offset => the bias added to the current time mark
 */
function current_datetime($offset = 0)
{
    return current_date($offset) . " " . current_time($offset);
}

/**
 * Current Decimals
 *
 * This function returns the current decimals to be added to the seconds as a
 * decimal part, this function uses the microtime function to get this level of
 * precision that can not be obtained using the original date and time functions
 *
 * @offset => the bias added to the current time mark
 * @size   => the size of the returned decimal part
 *
 * Notes:
 *
 * This function is used by current_datetime_decimals, and don't have more uses
 * that provice more precision in the logs files
 */
function current_decimals($offset = 0, $size = 4)
{
    $decimals = microtime(true) + (int)$offset;
    $decimals -= intval($decimals);
    $decimals = strval($decimals);
    $decimals = substr($decimals, 2, $size);
    $decimals = str_pad($decimals, $size, "0");
    return $decimals;
}

/**
 * Current DateTime Decimals
 *
 * This function returns the current date and time with decimals in the seconds
 * in a YYYY-MM-DD HH:II:SS.XXXX format, usefull when do you want to log information
 * more accuracy to debug issues, for example
 *
 * @offset => the bias added to the current time mark
 * @size   => the size used by the decimal part
 */
function current_datetime_decimals($offset = 0, $size = 4)
{
    return current_datetime($offset) . "." . current_decimals($offset, $size);
}

/**
 * Dateval
 *
 * This function try to do the same thing that intval or strval, but for date
 * values, to do this, this function try to separate all elements and identify
 * the year position and the other elements, the result will be of the format
 * YYYY-MM-DD
 *
 * @value => the input value to validate
 *
 * Notes:
 *
 * This function try to cast the year, month and day from 0000-00-00 to valid
 * values, this is because the databases accepts the 0000-00-00 date and is used
 * as emulated null, the month are limited to 12 and the day is limited to the
 * days of the month and year, this is usefull because the dates that are more
 * greather that zero, will have a valid and an existing value
 */
function dateval($value)
{
    $expr = ["-", ":", ", ", ".", "/"];
    $value = strval($value);
    $value = str_replace($expr, " ", $value);
    $value = prepare_words($value);
    $temp = explode(" ", $value);
    foreach ($temp as $key => $val) {
        $temp[$key] = intval($val);
    }
    for ($i = 0; $i < 3; $i++) {
        if (!isset($temp[$i])) {
            $temp[$i] = 0;
        }
    }
    if ($temp[2] > 1900) {
        $temp[2] = min(9999, max(0, $temp[2]));
        $temp[1] = min(12, max(0, $temp[1]));
        $temp[0] = min(__days_of_a_month($temp[2], $temp[1]), max(0, $temp[0]));
        $value = sprintf("%04d-%02d-%02d", $temp[2], $temp[1], $temp[0]);
    } else {
        $temp[0] = min(9999, max(0, $temp[0]));
        $temp[1] = min(12, max(0, $temp[1]));
        $temp[2] = min(__days_of_a_month($temp[0], $temp[1]), max(0, $temp[2]));
        $value = sprintf("%04d-%02d-%02d", $temp[0], $temp[1], $temp[2]);
    }
    return $value;
}

/**
 * Day of a Month helper
 *
 * This function is a helper used by other date and datetime functions, this
 * is usefull because allow to fix problems in dates that use days out of range
 *
 * @year  => year that you want to use in the validation
 * @month => month that you want to use in the validation
 */
function __days_of_a_month($year, $month)
{
    return date("t", strtotime(sprintf("%04d-%02d-%02d", $year, $month, 1)));
}

/**
 * Timeval
 *
 * This function try to do the same thing that intval or strval, but for time
 * values, to do this, this function try to separate all elements and identify
 * the elements, the result will be of the format HH:II:SS
 *
 * @value => the input value to validate
 */
function timeval($value)
{
    $expr = ["-", ":", ", ", ".", "/"];
    $value = strval($value);
    $value = str_replace($expr, " ", $value);
    $value = prepare_words($value);
    $temp = explode(" ", $value);
    foreach ($temp as $key => $val) {
        $temp[$key] = intval($val);
    }
    for ($i = 0; $i < 3; $i++) {
        if (!isset($temp[$i])) {
            $temp[$i] = 0;
        }
    }
    $temp[0] = min(24, max(0, $temp[0]));
    $temp[1] = min(59, max(0, $temp[1]));
    $temp[2] = min(59, max(0, $temp[2]));
    $value = sprintf("%02d:%02d:%02d", $temp[0], $temp[1], $temp[2]);
    return $value;
}

/**
 * Datetimeval
 *
 * This function try to do the same thing that intval or strval, but for datetime
 * values, to do this, this function try to separate all elements and identify
 * the year position and the other elements, the result will be of the format
 * YYYY-MM-DD HH:II:SS
 *
 * @value => the input value to validate
 *
 * Notes:
 *
 * This function try to cast the year, month and day from 0000-00-00 to valid
 * values, this is because the databases accepts the 0000-00-00 date and is used
 * as emulated null, the month are limited to 12 and the day is limited to the
 * days of the month and year, this is usefull because the dates that are more
 * greather that zero, will have a valid and an existing value
 */
function datetimeval($value)
{
    $expr = ["-", ":", ", ", ".", "/"];
    $value = strval($value);
    $value = str_replace($expr, " ", $value);
    $value = prepare_words($value);
    $temp = explode(" ", $value);
    foreach ($temp as $key => $val) {
        $temp[$key] = intval($val);
    }
    for ($i = 0; $i < 6; $i++) {
        if (!isset($temp[$i])) {
            $temp[$i] = 0;
        }
    }
    if ($temp[2] > 1900) {
        $temp[2] = min(9999, max(0, $temp[2]));
        $temp[1] = min(12, max(0, $temp[1]));
        $temp[0] = min(__days_of_a_month($temp[2], $temp[1]), max(0, $temp[0]));
        $temp[3] = min(23, max(0, $temp[3]));
        $temp[4] = min(59, max(0, $temp[4]));
        $temp[5] = min(59, max(0, $temp[5]));
        $value = sprintf(
            "%04d-%02d-%02d %02d:%02d:%02d",
            $temp[2], $temp[1], $temp[0], $temp[3], $temp[4], $temp[5]
        );
    } else {
        $temp[0] = min(9999, max(0, $temp[0]));
        $temp[1] = min(12, max(0, $temp[1]));
        $temp[2] = min(__days_of_a_month($temp[0], $temp[1]), max(0, $temp[2]));
        $temp[3] = min(23, max(0, $temp[3]));
        $temp[4] = min(59, max(0, $temp[4]));
        $temp[5] = min(59, max(0, $temp[5]));
        $value = sprintf(
            "%04d-%02d-%02d %02d:%02d:%02d",
            $temp[0], $temp[1], $temp[2], $temp[3], $temp[4], $temp[5]
        );
    }
    return $value;
}

/**
 * Time to Seconds
 *
 * This function converts the time format into seconds
 *
 * @time => time to be converted into seconds, the format will be HH:II:SS
 */
function __time2secs($time)
{
    $time = explode(":", $time);
    $secs = intval($time[0]) * 3600 + intval($time[1]) * 60 + intval($time[2]);
    return $secs;
}

/**
 * Seconds to Time
 *
 * This function converts the seconds into time format
 *
 * @secs => seconds to be converted to time format, the format will be a number
 */
function __secs2time($secs)
{
    $time = sprintf("%02d:%02d:%02d", intval($secs / 3600), intval(($secs / 60) % 60), intval($secs % 60));
    return $time;
}

/**
 * Current Day Of Week
 *
 * This function returns the current day of week as integer between 1 and 7
 * range, this is used by some functions in SaltOS, allow to specify a bias
 * used to move the current time mark
 *
 * @offset => the bias added to the current time mark
 */
function current_dow($offset = 0)
{
    return date("N", time() + (int)$offset);
}
