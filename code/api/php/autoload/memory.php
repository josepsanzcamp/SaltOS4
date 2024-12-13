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
 * Memory helper module
 *
 * This fie contains useful functions related to memory and time usage, allow to control the usage
 * of time and/or memory of a process, intended to prevent crashes in processes that requires more
 * time or memory resources that the defined by the system limits
 */

/**
 * Memory Get Free
 *
 * This function returns the free memory in bytes or the percentage of the memory_limit
 *
 * @bytes => if true, returns the free bytes, if false, returns the percentage
 */
function memory_get_free($bytes = false)
{
    $memory_limit = normalize_value(ini_get('memory_limit'));
    if ($memory_limit == -1) {
        $memory_limit = INF;
    }
    $memory_usage = memory_get_usage();
    $diff = $memory_limit - $memory_usage;
    if (!$bytes) {
        $diff = ($diff * 100) / $memory_limit;
        if ($memory_limit == INF) {
            $diff = 0;
        }
    }
    return $diff;
}

/**
 * Get Time Usage
 *
 * This function returns the time usage in seconds or in percentage of the max_execution_time
 *
 * @secs => if true, returns the used seconds, if false, returns the percentage
 */
function time_get_usage($secs = false)
{
    return __time_get_helper(__FUNCTION__, $secs);
}

/**
 * Get Free Time
 *
 * This function returns the free time in seconds or in percentage of the max_execution_time
 *
 * @secs => if true, returns the used seconds, if false, returns the percentage
 */
function time_get_free($secs = false)
{
    return __time_get_helper(__FUNCTION__, $secs);
}

/**
 * Init Time Get
 *
 * This function call the helper to initialize the static ini to the current microtime
 */
function init_timer()
{
    __time_get_helper(__FUNCTION__, false);
}

/**
 * Get Time helper
 *
 * This function is a helper of the time_get_usage and time_get_free functions, is used to
 * check the time usage and the free time that remain to finish the execution of the script
 */
function __time_get_helper($fn, $secs)
{
    static $ini = null;
    if (stripos($fn, 'init') !== false) {
        $ini = microtime(true);
        return;
    }
    $cur = microtime(true);
    $max = ini_get('max_execution_time');
    if (!$max) {
        $max = get_config('iniset/max_execution_time');
    }
    $diff = null;
    if (stripos($fn, 'usage') !== false) {
        $diff = $cur - $ini;
    } elseif (stripos($fn, 'free') !== false) {
        $diff = $max - ($cur - $ini);
    }
    if (!$secs) {
        $diff = ($diff * 100) / $max;
    }
    return $diff;
}

/**
 * Set Max Memory Limit
 *
 * This function is intended to do a ini_set with a more greather value to allow an
 * exceptionally amount of memory usage
 */
function set_max_memory_limit()
{
    $val = get_config('server/maxmemorylimit');
    if ($val) {
        ini_set('memory_limit', $val);
    }
}

/**
 * Set Max Execution Time
 *
 * This function is intended to do a ini_set with a more greather value to allow an
 * exceptionally amount of execution time
 */
function set_max_execution_time()
{
    $val = get_config('server/maxexecutiontime');
    if ($val) {
        ini_set('max_execution_time', $val);
    }
}
