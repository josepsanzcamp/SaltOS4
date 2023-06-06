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

/*
 *
 */
function eval_protected($input, $global = "", $source = "")
{
    if ($global != "") {
        foreach (explode(",", $global) as $var) {
            global $$var;
        }
    }
    $output = eval("return $input;");
    return $output;
}

/*
 *
 */
function set_array(&$array, $name, $value)
{
    if (!isset($array[$name])) {
        $array[$name] = $value;
    } else {
        $name .= "#";
        $last = array_key_last($array);
        $count = intval(substr($last, strlen($name))) + 1;
        while (isset($array[$name . $count])) {
            $count++;
        }
        $array[$name . $count] = $value;
    }
}

/*
 *
 */
function unset_array(&$array, $name)
{
    if (isset($array[$name])) {
        unset($array[$name]);
    }
    $name .= "#";
    $len = strlen($name);
    foreach ($array as $key => $val) {
        if (strncmp($name, $key, $len) == 0) {
            unset($array[$key]);
        }
    }
}

/*
 *
 */
function fix_key($arg)
{
    $pos = strpos($arg, "#");
    if ($pos !== false) {
        $arg = substr($arg, 0, $pos);
    }
    return $arg;
}

/*
 *
 */
function detect_recursion($fn)
{
    if (!is_array($fn)) {
        $fn = explode(",", $fn);
    }
    $temp = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    foreach ($temp as $key => $val) {
        if (isset($val["function"]) && in_array($val["function"], $fn)) {
            continue;
        }
        if (isset($val["file"]) && in_array(basename($val["file"]), $fn)) {
            continue;
        }
        unset($temp[$key]);
    }
    return count($temp);
}

/*
 *
 */
function xml2array($file, $usecache = true)
{
    static $depend = array();
    if (!file_exists($file)) {
        show_php_error(array("xmlerror" => "File not found: $file"));
    }
    if (!semaphore_acquire($file)) {
        show_php_error(array("xmlerror" => "Could not acquire the semaphore"));
    }
    if ($usecache) {
        if (detect_recursion(__FUNCTION__) == 1) {
            $depend = array();
        }
        $cache = get_cache_file($file, ".arr");
        if (cache_exists($cache, $file)) {
            $array = unserialize(file_get_contents($cache));
            if (isset($array["depend"]) && isset($array["root"])) {
                if (cache_exists($cache, $array["depend"])) {
                    $depend = array_merge($depend, $array["depend"]);
                    semaphore_release($file);
                    return $array["root"];
                }
            }
        }
    }
    $xml = file_get_contents($file);
    $data = xml2struct($xml, $file);
    $data = array_reverse($data);
    $array = struct2array($data, $file);
    //~ $array = struct2array_include($array);
    //~ if (detect_recursion(__FUNCTION__) == 1) {
        //~ $array = struct2array_path($array);
    //~ }
    if ($usecache) {
        $depend[] = $file;
        $array["depend"] = array_unique($depend);
        if (file_exists($cache)) {
            unlink($cache);
        }
        file_put_contents($cache, serialize($array));
        chmod($cache, 0666);
    }
    semaphore_release($file);
    return $array["root"];
}

/*
 *
 */
function xml2struct($xml, $file = "")
{
    // DETECT IF ENCODING ATTR IS FOUND
    $pos1 = strpos($xml, "<?xml");
    $pos2 = strpos($xml, "?>", $pos1);
    if ($pos1 !== false && $pos2 !== false) {
        $tag = substr($xml, $pos1, $pos2 + 2 - $pos1);
        $pos3 = strpos($tag, "encoding=");
        if ($pos3 !== false) {
            $pos4 = $pos3 + 9;
            if ($tag[$pos4] == '"') {
                $pos4++;
                $pos5 = strpos($tag, '"', $pos4);
            } elseif ($tag[$pos4] == "'") {
                $pos4++;
                $pos5 = strpos($tag, "'", $pos4);
            } else {
                $pos5 = strpos($tag, " ", $pos4);
                if ($pos5 > $pos2) {
                    $pos5 = $pos2;
                }
            }
            $xml = substr_replace($xml, "UTF-8", $pos1 + $pos4, $pos5 - $pos4);
        }
    }
    $xml = getutf8($xml);
    // CONTINUE
    $parser = xml_parser_create();
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
    $array = array();
    $index = array();
    xml_parse_into_struct($parser, $xml, $array, $index);
    $code = xml_get_error_code($parser);
    if ($code) {
        $error = xml_error_string($code);
        $linea = xml_get_current_line_number($parser);
        $fila = xml_get_current_column_number($parser);
        $file = basename($file);
        show_php_error(array("xmlerror" => "Error $code: $error (on file $file at line $linea,$fila)"));
    }
    xml_parser_free($parser);
    return $array;
}

/*
 *
 */
function struct2array(&$data, $file = "")
{
    $array = array();
    while ($linea = array_pop($data)) {
        $name = $linea["tag"];
        $type = $linea["type"];
        $value = "";
        if (isset($linea["value"])) {
            $value = $linea["value"];
        }
        $attr = array();
        if (isset($linea["attributes"])) {
            $attr = $linea["attributes"];
        }
        if ($type == "open") {
            // CASE 1 <some>
            $value = struct2array($data, $file);
            if (count($attr)) {
                $value = array("value" => $value,"#attr" => $attr);
            }
            set_array($array, $name, $value);
        } elseif ($type == "close") {
            // CASE 2 </some>
            return $array;
        } elseif ($type == "complete") {
            // CASE 3 <some/>
            // CASE 4 <some>some</some>
            if (count($attr)) {
                $value = array("value" => $value,"#attr" => $attr);
            }
            set_array($array, $name, $value);
        } elseif ($type == "cdata") {
            // NOTHING TO DO
        } else {
            $file = basename($file);
            show_php_error(array("xmlerror" => "Unknown tag type with name '&lt;/$name&gt;' (on file $file at line $linea)"));
        }
    }
    return $array;
}

/*
 *
 */
function eval_attr($array)
{
    if (!is_array($array)) {
        return eval_attr(array("inline" => $array))["inline"];
    }
    if (isset($array["value"]) && isset($array["#attr"])) {
        return eval_attr(array("inline" => $array))["inline"];
    }
    $result = array();
    foreach ($array as $key => $val) {
        if (is_array($val)) {
            if (isset($val["value"]) && isset($val["#attr"])) {
                $global = "";
                $value = $val["value"];
                $attr = $val["#attr"];
                $remove = 0;
                foreach ($attr as $key2 => $val2) {
                    $key3 = fix_key($key2);
                    switch ($key3) {
                        case "global":
                            $global = $val2;
                            foreach (explode(",", $global) as $var) {
                                global $$var;
                            }
                            unset($attr[$key2]);
                            break;
                        case "eval":
                            if (eval_bool($val2)) {
                                if (!$value) {
                                    show_php_error(array("xmlerror" => "Evaluation error: void expression"));
                                }
                                $value = eval_protected($value, $global);
                            }
                            unset($attr[$key2]);
                            break;
                        case "ifeval":
                            $val2 = eval_protected($val2, $global);
                            if (!$val2) {
                                $remove = 1;
                            }
                            unset($attr[$key2]);
                            break;
                    }
                }
                if (!$remove) {
                    $value  = eval_attr($value);
                    if (count($attr)) {
                        $result[$key] = array("value" => $value, "#attr" => $attr);
                    } else {
                        $result[$key] = $value;
                    }
                }
            } else {
                $result[$key] = eval_attr($val);
            }
        } else {
            $result[$key] = $val;
        }
    }
    return $result;
}

/*
 *
 */
function eval_bool($arg)
{
    static $bools = array(
        "1" => 1,
        "0" => 0,
        "" => 0,
        "true" => 1,
        "false" => 0,
        "on" => 1,
        "off" => 0,
        "yes" => 1,
        "no" => 0,
    );
    $bool = strtolower($arg);
    if (isset($bools[$bool])) {
        return $bools[$bool];
    }
    show_php_error(array("xmlerror" => "Unknown boolean value '$arg'"));
}
