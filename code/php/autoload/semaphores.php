<?php

/**
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

/**
 * Semaphore Acquire
 *
 * This function implement the acquire of a semaphore
 *
 * @name    => the name of the semaphore
 * @timeout => the timeout used in waiting operations
 */
function semaphore_acquire($name = "", $timeout = INF)
{
    return __semaphore_helper(__FUNCTION__, $name, $timeout);
}

/**
 * Semaphore Release
 *
 * This function implement the release of the semaphore
 *
 * @name => the name of the semaphore
 */
function semaphore_release($name = "")
{
    return __semaphore_helper(__FUNCTION__, $name, null);
}

/**
 * Semaphore Shutdown
 *
 * This function implement the shutdown of all semaphores, to do it,
 * the function will iterate in each semaphore to release and set to
 * null the semaphore pointer
 */
function semaphore_shutdown()
{
    return __semaphore_helper(__FUNCTION__, null, null);
}

/**
 * Semaphore File
 *
 * This function returns the associated semaphore file used by the
 * named semaphore, usefull for debug purposes
 *
 * @name => the name of the semaphore
 */
function semaphore_file($name = "")
{
    return __semaphore_helper(__FUNCTION__, $name, null);
}

/**
 * Semaphore helper
 *
 * This function implements the real semaphore functionalities, includes
 * the code to do an acquire, the release, the shutdown and to get the
 * file, is programmed as a function instead of a class by historical
 * motivation, in reality, the statics fds acts as a properties of a
 * class and each if stripos acts as a methods of a class
 *
 * @fn      => the function name that call the helper, to detect the feature
 * @name    => the name of the semaphore
 * @timeout => the timeout used in waiting operations
 */
function __semaphore_helper($fn, $name, $timeout)
{
    static $fds = [];
    if (stripos($fn, "acquire") !== false) {
        if ($name == "") {
            $name = __FUNCTION__;
        }
        $file = get_cache_file($name, ".sem");
        if (!is_writable(dirname($file))) {
            return false;
        }
        if (!isset($fds[$file])) {
            $fds[$file] = null;
        }
        if ($fds[$file]) {
            return false;
        }
        //~ capture_next_error();
        $fds[$file] = fopen($file, "a");
        //~ get_clear_error();
        if (!$fds[$file]) {
            return false;
        }
        chmod_protected($file, 0666);
        for (;;) {
            $result = flock($fds[$file], LOCK_EX | LOCK_NB);
            if ($result) {
                break;
            }
            $timeout -= __semaphore_usleep(rand(0, 1000));
            if ($timeout < 0) {
                fclose($fds[$file]);
                $fds[$file] = null;
                return false;
            }
        }
        ftruncate($fds[$file], 0);
        fwrite($fds[$file], gettrace([], true));
        return true;
    } elseif (stripos($fn, "release") !== false) {
        if ($name == "") {
            $name = __FUNCTION__;
        }
        $file = get_cache_file($name, ".sem");
        if (!isset($fds[$file])) {
            $fds[$file] = null;
        }
        if (!$fds[$file]) {
            return false;
        }
        flock($fds[$file], LOCK_UN);
        fclose($fds[$file]);
        $fds[$file] = null;
        return true;
    } elseif (stripos($fn, "shutdown") !== false) {
        foreach ($fds as $file => $fd) {
            if ($fds[$file]) {
                flock($fds[$file], LOCK_UN);
                fclose($fds[$file]);
                $fds[$file] = null;
            }
        }
        return true;
    } elseif (stripos($fn, "file") !== false) {
        if ($name == "") {
            $name = __FUNCTION__;
        }
        $file = get_cache_file($name, ".sem");
        return $file;
    }
    return false;
}

/**
 * Semaphore USleep helper
 *
 * This function implements an usleep (micro sleeper) using sockets, this
 * allow to break the execution of the function if a signal is received by
 * the process, in reality, the feature is powered by the socket_select that
 * is allowed to wait for read and write operations with a very precise
 * timeout.
 *
 * The returned value will be the difference between the end less the start,
 * in other words, the returned value is the ellapsed time sleeped by the
 * function
 *
 * @usec => the micro seconds that you want to sleep
 */
function __semaphore_usleep($usec)
{
    if (function_exists("socket_create")) {
        $socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
        $read = null;
        $write = null;
        $except = [$socket];
        $time1 = microtime(true);
        socket_select($read, $write, $except, intval($usec / 1000000), intval($usec % 1000000));
        $time2 = microtime(true);
        return ($time2 - $time1) * 1000000;
    }
    $time1 = microtime(true);
    usleep($usec);
    $time2 = microtime(true);
    return ($time2 - $time1) * 1000000;
}
