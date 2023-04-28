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

// phpcs:disable Generic.Files.LineLength

function show_php_error($array)
{
    // TRICK FOR EXHAUSTED MEMORY ERROR
    if (
        isset($array["phperror"]) &&
        words_exists("allowed memory size bytes exhausted tried allocate", $array["phperror"])
    ) {
        max_memory_limit();
    }
    // ADD BACKTRACE AND DEBUG IF NOT FOUND
    if (!isset($array["backtrace"])) {
        $array["backtrace"] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    }
    if (!isset($array["debug"])) {
        $array["debug"] = session_backtrace();
    }
    // CREATE THE MESSAGE ERROR USING HTML ENTITIES AND PLAIN TEXT
    $msg_text = do_message_error($array, "text");
    $hash = md5($msg_text);
    $dir = get_directory("dirs/logsdir", getcwd_protected() . "/data/logs");
    // REFUSE THE DEPRECATED WARNINGS
    if (isset($array["phperror"]) && stripos($array["phperror"], "deprecated") !== false) {
        if (is_writable($dir)) {
            $file = get_default("debug/deprecatedfile", "deprecated.log");
            if (!checklog($hash, $file)) {
                addlog($msg_text, $file);
            }
            addlog("***** {$hash} *****", $file);
        }
        return;
    }
    // ADD THE MSG_TEXT TO THE ERROR LOG FILE
    if (is_writable($dir)) {
        $file = get_default("debug/errorfile", "error.log");
        static $types = array(
            array("dberror","debug/dberrorfile","dberror.log"),
            array("phperror","debug/phperrorfile","phperror.log"),
            array("xmlerror","debug/xmlerrorfile","xmlerror.log"),
            array("jserror","debug/jserrorfile","jserror.log"),
            array("dbwarning","debug/dbwarningfile","dbwarning.log"),
            array("phpwarning","debug/phpwarningfile","phpwarning.log"),
            array("xmlwarning","debug/xmlwarningfile","xmlwarning.log"),
            array("jswarning","debug/jswarningfile","jswarning.log"),
        );
        foreach ($types as $type) {
            if (isset($array[$type[0]])) {
                $file = get_default($type[1], $type[2]);
                break;
            }
        }
        if (!checklog($hash, $file)) {
            addlog($msg_text, $file);
        }
        addlog("***** {$hash} *****", $file);
    }
    // PREPARE THE FINAL REPORT
    $msg_html = do_message_error($array, "html");
    $msg_json = do_message_error($array, "json");
    $msg = array(
        "error" => array(
            "text" => $msg_text,
            "html" => $msg_html,
            "json" => $msg_json,
            "hash" => $hash,
        )
    );
    output_handler(array(
        "data" => json_encode($msg),
        "type" => "application/json",
        "cache" => false
    ));
    die();
}

function do_message_error($array, $format)
{
    static $sep = array(
        "text" => "\n",
        "html" => "<br/>",
        "json" => "|",
    );
    static $types = array(
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
    );
    $msg = array();
    foreach ($array as $type => $data) {
        switch ($type) {
            case "dberror":
                $privated = array(
                    get_default("db/host"),
                    get_default("db/port"),
                    get_default("db/user"),
                    get_default("db/pass"),
                    get_default("db/name")
                );
                $data = str_replace($privated, "...", $data);
                break;
            case "backtrace":
                if (is_array($data)) {
                    foreach ($data as $key => $item) {
                        $temp = $key . " => " . $item["function"];
                        if (isset($item["class"])) {
                            $temp .= " (in class " . $item["class"] . ")";
                        }
                        if (isset($item["file"]) && isset($item["line"])) {
                            $temp .= " (in file " . basename($item["file"]) . ":" . $item["line"] . ")";
                        }
                        $data[$key] = $temp;
                    }
                    $data = implode($sep[$format], $data);
                } else {
                    $data = trim($data);
                }
                break;
            case "debug":
                if (is_array($data)) {
                    foreach ($data as $key => $item) {
                        $data[$key] = "{$key} => {$item}";
                    }
                    $data = implode($sep[$format], $data);
                } else {
                    $data = trim($data);
                }
                break;
        }
        if (!isset($types[$type])) {
            die("Unknown type $type");
        }
        if ($data != "") {
            $msg[] = array($types[$type],$data);
        }
    }
    if ($format == "text") {
        foreach ($msg as $key => $item) {
            $msg[$key] = "***** " . $item[0] . " *****" . $sep[$format] . $item[1];
        }
        $msg = implode($sep[$format], $msg);
    } elseif ($format == "html") {
        foreach ($msg as $key => $item) {
            $msg[$key] = "<h3>" . $item[0] . "</h3><pre>" . $item[1] . "</pre>";
        }
        $msg = implode($msg);
    //~ } elseif ($format == "json") {
        //~ foreach ($msg as $key => $item) {
            //~ $msg[$key] = $item[0] . $sep[$format] . $item[1];
        //~ }
        //~ $msg = implode($sep[$format], $msg);
    }
    return $msg;
}

function program_handlers()
{
    error_reporting(E_ALL);
    set_error_handler("__error_handler");
    set_exception_handler("__exception_handler");
    register_shutdown_function("__shutdown_handler");
}

function __error_handler($type, $message, $file, $line)
{
    show_php_error(array(
        "phperror" => "{$message} (code {$type})",
        "details" => "Error on file " . basename($file) . ":" . $line,
        "backtrace" => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
    ));
}

function __exception_handler($e)
{
    show_php_error(array(
        "exception" => $e->getMessage() . " (code " . $e->getCode() . ")",
        "details" => "Error on file " . basename($e->getFile()) . ":" . $e->getLine(),
        "backtrace" => $e->getTrace()
    ));
}

function __shutdown_handler()
{
    $error = error_get_last();
    $types = array(E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR,E_USER_ERROR,E_RECOVERABLE_ERROR);
    if (is_array($error) && isset($error["type"]) && in_array($error["type"], $types)) {
        show_php_error(array(
            "phperror" => "{$error["message"]}",
            "details" => "Error on file " . basename($error["file"]) . ":" . $error["line"],
            "backtrace" => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
        ));
    }
    semaphore_shutdown();
}

//~ function pretty_html_error($msg)
//~ {
    //~ $html = "<!DOCTYPE html>";
    //~ $html .= "<html>";
    //~ $html .= "<head>";
    //~ $html .= "<title>" . get_name_version_revision() . "</title>";
    //~ $html .= "<style>";
    //~ $html .= ".phperror { color:#fff; background:#00a; margin:0; padding:10px; font-family:monospace; }";
    //~ $html .= ".phperror form { display:inline; float:right; }";
    //~ $html .= ".phperror input { background:#fff; color:#00f; font-weight:bold; border:0; ";
    //~ $html .= "padding:10px 20px; font-family:monospace; margin-left:10px; }";
    //~ $html .= ".phperror input:hover { background:#000; color:#fff; cursor:pointer; }";
    //~ $html .= ".phperror h1 { display:inline; }";
    //~ $html .= ".phperror pre { white-space:normal; }";
    //~ $html .= "</style>";
    //~ $html .= "</head>";
    //~ $html .= "<body class='phperror'>";
    //~ $html .= "<h1>" . get_name_version_revision() . "</h1>";
    //~ $html .= $msg;
    //~ $html .= "</body>";
    //~ $html .= "</html>";
    //~ return $html;
//~ }

//~ function upload_error2string($error)
//~ {
    //~ static $errors = array(
        //~ UPLOAD_ERR_OK => "UPLOAD_ERR_OK",                 // 0
        //~ UPLOAD_ERR_INI_SIZE => "UPLOAD_ERR_INI_SIZE",     // 1
        //~ UPLOAD_ERR_FORM_SIZE => "UPLOAD_ERR_FORM_SIZE",   // 2
        //~ UPLOAD_ERR_PARTIAL => "UPLOAD_ERR_PARTIAL",       // 3
        //~ UPLOAD_ERR_NO_FILE => "UPLOAD_ERR_NO_FILE",       // 4
        //~ UPLOAD_ERR_NO_TMP_DIR => "UPLOAD_ERR_NO_TMP_DIR", // 6
        //~ UPLOAD_ERR_CANT_WRITE => "UPLOAD_ERR_CANT_WRITE", // 7
        //~ UPLOAD_ERR_EXTENSION => "UPLOAD_ERR_EXTENSION"    // 8
    //~ );
    //~ if (isset($errors[$error])) {
        //~ return $errors[$error];
    //~ }
    //~ return "UPLOAD_ERR_UNKWOWN";
//~ }

// TODO: REVISAR ESTA FUNCION
//~ function parse_error2array($error)
//~ {
    //~ $array = array();
    //~ $pos = 0;
    //~ $len = strlen($error);
    //~ while ($pos < $len) {
        //~ if (substr($error, $pos, 4) == "<h3>") {
            //~ $pos = $pos + 4;
            //~ $pos2 = strpos($error, "</h3>", $pos);
            //~ $array[] = substr($error, $pos, $pos2 - $pos);
            //~ $pos = $pos2 + 5;
        //~ } elseif (substr($error, $pos, 5) == "<pre>") {
            //~ $pos = $pos + 5;
            //~ $pos2 = strpos($error, "</pre>", $pos);
            //~ $array[] = substr($error, $pos, $pos2 - $pos);
            //~ $pos = $pos2 + 6;
        //~ } else {
            //~ break;
        //~ }
    //~ }
    //~ return $array;
//~ }
