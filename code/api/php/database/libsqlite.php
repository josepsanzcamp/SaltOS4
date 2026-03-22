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
 * SQLite3 functions library
 *
 * SQLite's database allow to define external functions that can be used from the SQL language,
 * this is a great feature that allow to use SQLite as MySQL, and using this feature of the
 * database, the SQLite drivers use the libsqlite to add a lot of features found in MySQL and
 * used in a lot of queries by SaltOS
 *
 * More info about this feature by searching:
 * @PDO     => sqliteCreateFunction/sqliteCreateAggregate
 * @SQLite3 => createFunction/createAggregate
 */

/**
 * GROUP_CONCAT
 *
 * This function add the GROUP_CONCAT to the SQLite database
 */
function __libsqlite_group_concat_step($context, $rows, $string, $separator = ',')
{
    if ($context !== '') {
        $context .= $separator;
    }
    $context .= $string;
    return $context;
}

/**
 * GROUP_CONCAT
 *
 * This function add the GROUP_CONCAT to the SQLite database
 */
function __libsqlite_group_concat_finalize($context, $rows)
{
    return $context;
}

/**
 * REPLACE
 *
 * This function add the REPLACE to the SQLite database
 */
function __libsqlite_replace($subject, $search, $replace)
{
    return str_replace($search, $replace, $subject);
}

/**
 * LPAD
 *
 * This function add the LPAD to the SQLite database
 */
function __libsqlite_lpad($input, $length, $char)
{
    return str_pad(strval($input), intval($length), strval($char), STR_PAD_LEFT);
}

/**
 * CONCAT
 *
 * This function add the CONCAT to the SQLite database
 */
function __libsqlite_concat(...$args)
{
    return implode('', $args);
}

/**
 * CONCAT_WS
 *
 * This function add the CONCAT_WS to the SQLite database
 */
function __libsqlite_concat_ws(...$args)
{
    $separator = array_shift($args);
    $args = array_diff($args, [null]);
    return implode($separator, $args);
}

/**
 * UNIX_TIMESTAMP
 *
 * This function add the UNIX_TIMESTAMP to the SQLite database
 */
function __libsqlite_unix_timestamp($date)
{
    return strtotime($date);
}

/**
 * FROM_UNIXTIME
 *
 * This function add the FROM_UNIXTIME to the SQLite database
 */
function __libsqlite_from_unixtime($timestamp)
{
    return date('Y-m-d H:i:s', $timestamp);
}

/**
 * YEAR
 *
 * This function add the YEAR to the SQLite database
 */
function __libsqlite_year($date)
{
    return intval(date('Y', strtotime($date)));
}

/**
 * MONTH
 *
 * This function add the MONTH to the SQLite database
 */
function __libsqlite_month($date)
{
    return intval(date('m', strtotime($date)));
}

/**
 * WEEK
 *
 * This function add the WEEK to the SQLite database
 */
function __libsqlite_week($date, $mode = 0)
{
    $mode = $mode * 86400;
    return intval(date('W', strtotime($date) + $mode));
}

/**
 * TRUNCATE
 *
 * This function add the TRUNCATE to the SQLite database
 */
function __libsqlite_truncate($n, $d)
{
    $d = pow(10, $d);
    return strval(intval($n * $d) / $d);
}

/**
 * DAY
 *
 * This function add the DAY to the SQLite database
 */
function __libsqlite_day($date)
{
    return intval(date('d', strtotime($date)));
}

/**
 * DAYOFYEAR
 *
 * This function add the DAYOFYEAR to the SQLite database
 */
function __libsqlite_dayofyear($date)
{
    return date('z', strtotime($date)) + 1;
}

/**
 * DAYOFWEEK
 *
 * This function add the DAYOFWEEK to the SQLite database
 */
function __libsqlite_dayofweek($date)
{
    return date('w', strtotime($date)) + 1;
}

/**
 * HOUR
 *
 * This function add the HOUR to the SQLite database
 */
function __libsqlite_hour($date)
{
    return intval(date('H', strtotime($date)));
}

/**
 * MINUTE
 *
 * This function add the MINUTE to the SQLite database
 */
function __libsqlite_minute($date)
{
    return intval(date('i', strtotime($date)));
}

/**
 * SECOND
 *
 * This function add the SECOND to the SQLite database
 */
function __libsqlite_second($date)
{
    return intval(date('s', strtotime($date)));
}

/**
 * MD5
 *
 * This function add the MD5 to the SQLite database
 */
function __libsqlite_md5($temp)
{
    return md5($temp);
}

/**
 * REPEAT
 *
 * This function add the REPEAT to the SQLite database
 */
function __libsqlite_repeat($str, $count)
{
    return str_repeat(strval($str), intval($count));
}

/**
 * FIND_IN_SET
 *
 * This function add the FIND_IN_SET to the SQLite database
 */
function __libsqlite_find_in_set($str, $strlist)
{
    $index = array_search(strval($str), explode(',', strval($strlist)), true);
    return $index !== false ? $index + 1 : 0;
}

/**
 * IF
 *
 * This function add the IF to the SQLite database
 */
function __libsqlite_if($condition, $value_if_true, $value_if_false)
{
    return $condition ? $value_if_true : $value_if_false;
}

/**
 * POW
 *
 * This function add the POW to the SQLite database
 */
function __libsqlite_pow($base, $exp)
{
    return floatval(pow($base, $exp));
}

/**
 * DATE_FORMAT
 *
 * This function add the DATE_FORMAT to the SQLite database
 */
function __libsqlite_date_format($date, $format)
{
    return date(str_replace('%', '', $format), strtotime($date));
}

/**
 * NOW
 *
 * This function add the NOW to the SQLite database
 */
function __libsqlite_now()
{
    return date('Y-m-d H:i:s');
}
