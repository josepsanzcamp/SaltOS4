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
 * Show PHP Error
 *
 * This function allow to SaltOS to trigger the errors in a multiple levels:
 *
 * 1) Check if the error is caused by a memory allocation error, and in this case, try
 * to setup more memory to continue executing the error function, sometimes if the system
 * is using all memory, this function can not do all tasks and don't know whats be happening
 *
 * 2) Add some extra traces if they are not found in the input array
 *
 * 3) Create a human readable message in text and json format, the text will be used
 * to log the error using a regular file and the json will be used as stdout response
 *
 * 4) If the error is caused by a deprecation, the error will be logged in the log file
 * but the execution of the code will continue (if it can continue!!!)
 *
 * 5) Try to categorize the error and log the text in the specific log file, this part
 * is optimized to prevent the addition of repeated errors using a hash as a trick
 *
 * 6) Send a json to the stdout using the output handler.
 *
 * THs input @array can contain pairs of key val:
 *
 * @dberror    => The text used in the DB Error section
 * @phperror   => The text used in the PHP Error section
 * @xmlerror   => The text used in the XML Error section
 * @jserror    => The text used in the JS Error section
 * @dbwarning  => The text used in the DB Warning section
 * @phpwarning => The text used in the PHP Warning section
 * @xmlwarning => The text used in the XML Warning section
 * @jswarning  => The text used in the JS Warning section
 * @source     => The text used in the Source section
 * @exception  => The text used in the Exception section
 * @details    => The text used in the Details section
 * @query      => The text used in the Query section
 * @backtrace  => The text used in the Backtrace section
 * @debug      => The text used in the Debug section
 *
 * Notes:
 *
 * The unset for the pid and the time keys of the debug array is justificate
 * because each execution modify the pid and the time entries and break the
 * optimization of the hash with the checklog to prevent repetitions in the
 * log file
 */
function show_php_error($array)
{
    // Trick for exhausted memory error
    if (
        isset($array["phperror"]) &&
        words_exists("allowed memory size bytes exhausted tried allocate", $array["phperror"])
    ) {
        set_max_memory_limit();
    }
    // Add backtrace and debug if not found
    if (!isset($array["backtrace"])) {
        $array["backtrace"] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    }
    if (!isset($array["debug"])) {
        $array["debug"] = session_backtrace();
        unset($array["debug"]["pid"]);
        unset($array["debug"]["time"]);
    }
    // Create the message error using html entities and plain text
    $msg = do_message_error($array);
    $msg_text = $msg["text"];
    $msg_json = $msg["json"];
    $hash = md5($msg_text);
    $dir = get_directory("dirs/logsdir") ?? getcwd_protected() . "/data/logs/";
    // Refuse the deprecated warnings
    if (isset($array["phperror"]) && stripos($array["phperror"], "deprecated") !== false) {
        if (is_writable($dir)) {
            $file = get_config("debug/deprecatedfile") ?? "deprecated.log";
            if (!checklog($hash, $file)) {
                addlog($msg_text, $file);
            }
            addlog("***** {$hash} *****", $file);
        }
        return;
    }
    // Add the msg_text to the error log file
    if (is_writable($dir)) {
        $file = get_config("debug/errorfile") ?? "error.log";
        $types = [
            ["dberror", "debug/dberrorfile", "dberror.log"],
            ["phperror", "debug/phperrorfile", "phperror.log"],
            ["xmlerror", "debug/xmlerrorfile", "xmlerror.log"],
            ["jserror", "debug/jserrorfile", "jserror.log"],
            ["dbwarning", "debug/dbwarningfile", "dbwarning.log"],
            ["phpwarning", "debug/phpwarningfile", "phpwarning.log"],
            ["xmlwarning", "debug/xmlwarningfile", "xmlwarning.log"],
            ["jswarning", "debug/jswarningfile", "jswarning.log"],
        ];
        foreach ($types as $type) {
            if (isset($array[$type[0]])) {
                $file = get_config($type[1]) ?? $type[2];
                break;
            }
        }
        if (!checklog($hash, $file)) {
            addlog($msg_text, $file);
        }
        addlog("***** {$hash} *****", $file);
    }
    // Trick to clear previous data
    while (ob_get_level()) {
        ob_end_clean();
    }
    // Prepare the final report
    output_handler_json([
        "error" => $msg_json,
    ]);
}

/**
 * Do Message Error
 *
 * This function acts as a helper of the show_php_error, is intended to build
 * the text and the json messages used to the log file and for the stdout channel
 *
 * THs input @array can contain pairs of key val:
 *
 * @dberror    => The text used in the DB Error section
 * @phperror   => The text used in the PHP Error section
 * @xmlerror   => The text used in the XML Error section
 * @jserror    => The text used in the JS Error section
 * @dbwarning  => The text used in the DB Warning section
 * @phpwarning => The text used in the PHP Warning section
 * @xmlwarning => The text used in the XML Warning section
 * @jswarning  => The text used in the JS Warning section
 * @source     => The text used in the Source section
 * @exception  => The text used in the Exception section
 * @details    => The text used in the Details section
 * @query      => The text used in the Query section
 * @backtrace  => The text used in the Backtrace section
 * @debug      => The text used in the Debug section
 *
 * Returns an array with the text and the json formated output ready to be used
 * in the log file and in the stdout channel
 */
function do_message_error($array)
{
    $json = [
        "text" => "",
        "code" => "",
    ];
    // Prepare json version
    foreach ($array as $type => $data) {
        switch ($type) {
            case "dberror":
                $privated = [
                    get_config("db/host"),
                    get_config("db/port"),
                    get_config("db/user"),
                    get_config("db/pass"),
                    get_config("db/name"),
                ];
                $data = str_replace($privated, "...", $data);
                break;
            case "backtrace":
                if (is_array($data)) {
                    $json["code"] = __get_code_from_trace($data);
                    foreach ($data as $key => $item) {
                        $temp = $item["function"];
                        if (isset($item["class"])) {
                            $temp .= " (in class " . $item["class"] . ")";
                        }
                        if (isset($item["file"]) && isset($item["line"])) {
                            $temp .= " (in file " . basename($item["file"]) . ":" . $item["line"] . ")";
                        }
                        $data[$key] = $temp;
                    }
                }
                if (is_string($data)) {
                    $data = trim($data);
                }
                break;
            case "debug":
                if (is_string($data)) {
                    $data = trim($data);
                }
                break;
        }
        if (is_array($data) && !count($data)) {
            unset($array[$type]);
        } elseif (is_string($data) && $data == "") {
            unset($array[$type]);
        } elseif ($type == "code") {
            unset($array[$type]);
            $json[$type] = $data;
        } elseif (is_string($data) && $json["text"] == "") {
            $array[$type] = $data;
            $json["text"] = $data;
        } else {
            $array[$type] = $data;
        }
    }
    // Prepare html version
    $types = [
        "dberror" => "DB Error",
        "phperror" => "PHP Error",
        "xmlerror" => "XML Error",
        "jserror" => "JS Error",
        "dbwarning" => "DB Warning",
        "phpwarning" => "PHP Warning",
        "xmlwarning" => "XML Warning",
        "jswarning" => "JS Warning",
        "source" => "Source",
        "exception" => "Exception",
        "details" => "Details",
        "query" => "Query",
        "backtrace" => "Backtrace",
        "debug" => "Debug",
    ];
    $text = [];
    foreach ($array as $type => $data) {
        switch ($type) {
            case "backtrace":
                if (is_array($data)) {
                    foreach ($data as $key => $item) {
                        $data[$key] = "{$key} => {$item}";
                    }
                    $data = implode("\n", $data);
                }
                break;
            case "debug":
                if (is_array($data)) {
                    foreach ($data as $key => $item) {
                        $data[$key] = "{$key} => {$item}";
                    }
                    $data = implode("\n", $data);
                }
                break;
        }
        if (!isset($types[$type])) {
            die("Unknown type $type");
        }
        $text[] = [$types[$type], $data];
    }
    foreach ($text as $key => $item) {
        $text[$key] = "***** " . $item[0] . " *****" . "\n" . $item[1];
    }
    $text = implode("\n", $text);
    return [
        "text" => $text,
        "json" => $json,
    ];
}

/**
 * Program Handlers
 *
 * This function program all error handlers
 */
function program_handlers()
{
    error_reporting(E_ALL);
    set_error_handler("__error_handler");
    set_exception_handler("__exception_handler");
    register_shutdown_function("__shutdown_handler");
}

/**
 * Error Handler
 *
 * This function is the callback function used by the set_error_handler
 *
 * Ths arguments are defined by the set_error_handler:
 *
 * @type    => The code of the error
 * @message => The descriptive message of the error
 * @file    => The filename of the file that trigger the error
 * @line    => The line where the error will occurred
 */
function __error_handler($type, $message, $file, $line)
{
    show_php_error([
        "phperror" => "{$message} (code {$type})",
        "details" => "Error on file " . basename($file) . ":" . $line,
        "backtrace" => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
        "code" => pathinfo($file, PATHINFO_FILENAME) . ":" . $line,
    ]);
}

/**
 * Exception Handler
 *
 * This function is the callback function used by the set_exception_handler
 *
 * Ths arguments are defined by the set_exception_handler:
 *
 * @e => object that contains the getMessage, getCode, getFile, getLine and getTrace
 *       methods
 */
function __exception_handler($e)
{
    show_php_error([
        "exception" => $e->getMessage() . " (code " . $e->getCode() . ")",
        "details" => "Error on file " . basename($e->getFile()) . ":" . $e->getLine(),
        "backtrace" => $e->getTrace(),
        "code" => pathinfo($e->getFile(), PATHINFO_FILENAME) . ":" . $e->getLine(),
    ]);
}

/**
 * Shutdown Handler
 *
 * This function is the callback function used by the register_shutdown_function, try to
 * detect if an error is the cause of the shutdown of the script, note that a correct
 * execution will execute this function and only it must to trigger an error if a real
 * error is in the stack of the errors events, to do it this function uses the error_get_last
 * to check if the value in in the list of typified errors
 */
function __shutdown_handler()
{
    $error = error_get_last();
    $types = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR];
    if (is_array($error) && isset($error["type"]) && in_array($error["type"], $types)) {
        show_php_error([
            "phperror" => "{$error["message"]}",
            "details" => "Error on file " . basename($error["file"]) . ":" . $error["line"],
            "backtrace" => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
            "code" => pathinfo($error["file"], PATHINFO_FILENAME) . ":" . $error["line"],
        ]);
    }
    semaphore_shutdown();
}

/**
 * Get Code From Trace
 *
 * This function acts as helper of the show_json_error, and try to get the filename and the line
 * where the error will be triggered, for example, an error triggered from the index.php at line
 * 23 will generate a code index:23, this information will be usefull for our technical service
 * to help the users when live issues with our API
 *
 * @trace => the array returned by the debug_backtrace function
 * @index => the position of the array used to get the filename and the line
 */
function __get_code_from_trace($trace, $index = 0)
{
    $code = "unknown:0";
    if (isset($trace[$index])) {
        $trace = $trace[$index];
        if (isset($trace["file"]) && isset($trace["line"])) {
            $code = pathinfo($trace["file"], PATHINFO_FILENAME) . ":" . $trace["line"];
        }
    }
    return $code;
}

/**
 * Show JSON Error
 *
 * This function is triggered from the code in a controlate errors, the idea is to have
 * a simple way to send controled errors to the user using a json output channel, and to
 * do it, we have this function that can be called with a simple message and the code
 * is created automatically to help the backtrace of the issues
 *
 * @msg => this contains a simple text that is used in the json output
 */
function show_json_error($msg)
{
    // Trick to clear previous data
    while (ob_get_level()) {
        ob_end_clean();
    }
    // Prepare the final report
    output_handler_json([
        "error" => [
            "text" => $msg,
            "code" => __get_code_from_trace(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)),
        ],
    ]);
}
