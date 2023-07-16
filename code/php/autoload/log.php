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
 * Check Log
 *
 * This function is a helper for the show_php_error, allow to detect repetitions
 * of the same text in the log file to prevent to add repeated lines, the usage
 * is very simple, only requires a hash and a file to check that the hash is not
 * found in the contents of the file, you can think in this function as a grep
 * replacement that is able to found the hash in the file
 *
 * @hash => the pattern that you want to search in the file
 * @file => the file where search the pattern
 */
function checklog($hash, $file)
{
    $dir = get_directory("dirs/logsdir", getcwd_protected() . "/data/logs");
    if (
        file_exists($dir . $file) &&
        is_file($dir . $file) &&
        filesize($dir . $file) < memory_get_free(true) / 3
    ) {
        $buffer = file_get_contents($dir . $file);
        if (strpos($buffer, $hash) !== false) {
            return 1;
        }
    }
    return 0;
}

/**
 * Add Log
 *
 * This function add messages to the specified log file
 *
 * @msg  => message that you want to add to the log file
 * @file => the log file that you want to use without directory
 *
 * Notes:
 *
 * If not file is specified, the debug/logfile (saltos.log) is used by default
 *
 * The logs files are stored in the logsdir (/data/logs)
 *
 * This function performs the log rotation is the maxfilesize is reached
 */
function addlog($msg, $file = "")
{
    if (!$file) {
        $file = get_config("debug/logfile", "saltos.log");
    }
    $dir = get_directory("dirs/logsdir", getcwd_protected() . "/data/logs");
    $maxfilesize = normalize_value(get_config("debug/maxfilesize", "1M"));
    if (
        $maxfilesize > 0 &&
        file_exists($dir . $file) &&
        is_file($dir . $file) &&
        filesize($dir . $file) >= $maxfilesize
    ) {
        $next = 1;
        while (file_exists($dir . $file . "." . $next)) {
            $next++;
        }
        rename($dir . $file, $dir . $file . "." . $next);
    }
    $msg = trim($msg);
    $msg = explode("\n", $msg);
    $pre = current_datetime_decimals();
    foreach ($msg as $key => $val) {
        $msg[$key] = $pre . ": " . $val;
    }
    $msg = implode("\n", $msg) . "\n";
    file_put_contents($dir . $file, $msg, FILE_APPEND);
    chmod_protected($dir . $file, 0666);
}

/**
 * Add Trace
 *
 * This function performs the addlog to the file using as input the array, the
 * main idea is to pass the same array that the used in the show_php_error, the
 * difference is that addtrace, only add the backtrace and debug to the array
 * and then, saves the log to the specified file
 *
 * @array => the array that can contains the same info that show_php_error
 * @file  => the file where do you want to store the log contents
 */
function addtrace($array, $file)
{
    addlog(gettrace($array), $file);
}

/**
 * Get Trace
 *
 * This function get an array as show_php_error, add the backtrace and debug
 * information and convert all array into a string
 *
 * @array => the array that can contains the same info that show_php_error
 */
function gettrace($array)
{
    if (!isset($array["backtrace"])) {
        $array["backtrace"] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    }
    if (!isset($array["debug"])) {
        $array["debug"] = session_backtrace();
    }
    $msg = do_message_error($array);
    return $msg["text"];
}

/**
 * Session Backtrace
 *
 * Returns a string with the pid, sessid and current datetime with decimals
 *
 * Notes:
 *
 * The fields of this array allow to do low level debug processes, this data is
 * generally used by the semaphores and some forced addtrace calls, but causes
 * problems in the error reporting because break the hash and checklog optimization
 */
function session_backtrace()
{
    $array = array(
        "pid" => getmypid(),
        "time" => current_datetime_decimals(),
        "token" => get_server("HTTP_TOKEN"),
    );
    $array = array_diff($array, array(""));
    return $array;
}
