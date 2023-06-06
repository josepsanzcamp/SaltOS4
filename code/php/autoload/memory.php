<?php

/*
 ____        _ _    ___  ____    _  _    ___
/ ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
\___ \ / _` | | __| | | \___ \  | || |_| | | |
 ___) | (_| | | |_| |_| |___) | |__   _| |_| |
|____/ \__,_|_|\__|\___/|____/     |_|(_)___/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2023 by Josep Sanz Campderrós
More information in https://www.saltos.org or info@saltos.org

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

declare(strict_types=1);

/*
 *
 */
function memory_get_free($bytes = false)
{
    $memory_limit = normalize_value(ini_get("memory_limit"));
    $memory_usage = memory_get_usage();
    $diff = $memory_limit - $memory_usage;
    if (!$bytes) {
        $diff = ($diff * 100) / $memory_limit;
    }
    return $diff;
}

/*
 *
 */
function time_get_usage($secs = false)
{
    return __time_get_helper(__FUNCTION__, $secs);
}

/*
 *
 */
function time_get_free($secs = false)
{
    return __time_get_helper(__FUNCTION__, $secs);
}

/*
 *
 */
function __time_get_helper($fn, $secs)
{
    static $ini = null;
    if ($ini === null) {
        $ini = microtime(true);
    }
    $cur = microtime(true);
    $max = ini_get("max_execution_time");
    if (!$max) {
        $max = get_default("ini_set/max_execution_time");
    }
    if (stripos($fn, "usage") !== false) {
        $diff = $cur - $ini;
    } elseif (stripos($fn, "free") !== false) {
        $diff = $max - ($cur - $ini);
    }
    if (!$secs) {
        $diff = ($diff * 100) / $max;
    }
    return $diff;
}

/*
 *
 */
function max_memory_limit()
{
    ini_set("memory_limit", get_default("server/maxmemorylimit"));
}

/*
 *
 */
function max_execution_time()
{
    ini_set("max_execution_time", get_default("server/maxexecutiontime"));
}
