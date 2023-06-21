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
 * OB Passthru
 *
 * This function is a join of ob_start and passthru, the main idea
 * is to execute a program using the command line and get the
 * output (only stdout and not stderr) and return the data
 *
 * @cmd => the command line that you want to execute
 * @expires => the expires time used to compute if the cache is valid
 *
 * This funtion tries to execute the command using some of the
 * provided methods, passthru, system, exec and shell_exec, another
 * feature is that the function detects what command are available
 * to use
 */
function ob_passthru($cmd, $expires = 0)
{
    if ($expires) {
        $cache = get_cache_file($cmd, ".out");
        if (file_exists($cache) && is_file($cache)) {
            $mtime = filemtime($cache);
            if (time() - $expires < $mtime) {
                return file_get_contents($cache);
            }
        }
    }
    if (!is_disabled_function("passthru")) {
        ob_start();
        passthru($cmd);
        $buffer = ob_get_clean();
    } elseif (!is_disabled_function("system")) {
        ob_start();
        system($cmd);
        $buffer = ob_get_clean();
    } elseif (!is_disabled_function("exec")) {
        $buffer = array();
        exec($cmd, $buffer);
        $buffer = implode("\n", $buffer);
    } elseif (!is_disabled_function("shell_exec")) {
        ob_start();
        $buffer = shell_exec($cmd);
        ob_get_clean();
    } else {
        $buffer = "";
    }
    if ($expires) {
        file_put_contents($cache, $buffer);
        chmod($cache, 0666);
    }
    return $buffer;
}

/**
 * Check Commands
 *
 * This function tries to validate if the commands are available
 * in the system, to do it, uses the unix command witch
 *
 * @commands => the commands that you want to check if are they available
 * @expires => the expires time used to compute if the cache is valid
 */
function check_commands($commands, $expires = 0)
{
    if (!is_array($commands)) {
        $commands = explode(",", $commands);
    }
    $result = 1;
    foreach ($commands as $command) {
        $result &= ob_passthru(str_replace(
            array("__INPUT__"),
            array($command),
            get_config("commands/__which__", "which __INPUT__")
        ), $expires) ? 1 : 0;
    }
    return $result;
}

/**
 * Is Disabled Function
 *
 * This function check if the argument contains a disabled
 * function, this feature uses the variables disable_functions
 * and suhosin.executor.func.blacklist to get the list of all
 * disabled functions
 *
 * @fn => the function that you want to check if is it disabled
 */
function is_disabled_function($fn = "")
{
    static $disableds_string = null;
    static $disableds_array = array();
    if ($disableds_string === null) {
        $disableds_string = ini_get("disable_functions") . "," . ini_get("suhosin.executor.func.blacklist");
        $disableds_array = $disableds_string ? explode(",", $disableds_string) : array();
        foreach ($disableds_array as $key => $val) {
            $val = strtolower(trim($val));
            if ($val == "") {
                unset($disableds_array[$key]);
            }
            if ($val != "") {
                $disableds_array[$key] = $val;
            }
        }
    }
    return in_array($fn, $disableds_array);
}

/**
 * Exec Timeout
 *
 * This helper function allow to execute commands using the external
 * command timeout, this unix command allow to define the timeout for
 * an execution of other command, and when the timeout is reached, then
 * break the execution killing the process
 *
 * @cmd => the command that you want to execute with a timeout control
 *
 * Returns the string that contains the command with ths timeout control
 */
function __exec_timeout($cmd)
{
    if (check_commands(get_config("commands/timeout"), 60)) {
        $cmd = str_replace(
            array("__TIMEOUT__","__COMMAND__"),
            array(get_config("commands/commandtimeout", 60),$cmd),
            get_config("commands/__timeout__", "timeout __TIMEOUT__ __COMMAND__")
        );
    }
    return $cmd;
}
