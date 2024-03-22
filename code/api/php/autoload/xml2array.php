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
 * XML to Array helper module
 *
 * This fie contains useful functions related to the xml manipulation, this code is a part of the
 * main version of the SaltOS because the main idea defined some years ago continues active, if you
 * compare this code, you can see more accuracy in the specification to be more specific and precise
 * but this code is a part of all the SaltOS versions
 */

/**
 * Eval Protected
 *
 * This function allow to execute PHP code using the eval function in a controlled
 * environment, you can specify some global variables to improve the eval execution
 *
 * @input  => the code to be executed
 * @global => the list (separated by comma) of global variables that you want to use
 */
function eval_protected($input, $global = "")
{
    if ($global != "") {
        foreach (explode(",", $global) as $var) {
            global $$var;
        }
    }
    $output = eval("return $input;");
    return $output;
}

/**
 * Set Array
 *
 * This function allow to specify multiples entries in an array with the same key,
 * to do this, the function will add #num where num is a unique number, in reality
 * if you want to set multiples entries for the key "test", you get in reality an
 * array with entries as "test", "test#1", "test#2"
 *
 * This function works in concordance of the fix_key, that is able to get the key
 * as "test#1" and return only "test" that is the original key without the suffix
 * added to allow multiples instances of the same key in an associative array
 *
 * @array => array that you want to add the key with the value (by reference)
 * @name  => the key used in the array, if exists, it will try to add the suffix to
 *           prevent collisions
 * @value => the value that you want to set in this position of the array
 */
function set_array(&$array, $name, $value)
{
    if (!isset($array[$name])) {
        $array[$name] = $value;
    } else {
        $name .= "#";
        $len = strlen($name);
        $key = array_key_last($array);
        if (strncmp($name, $key, $len) == 0) {
            $count = intval(substr($key, $len)) + 1;
        } else {
            $count = 1;
        }
        while (isset($array[$name . $count])) {
            $count++;
        }
        $array[$name . $count] = $value;
    }
}

/**
 * Unset Array
 *
 * This function remove all entries of the array that matches with the name of
 * the key, for example, if you specify the name "test", the function unset all
 * entries as "test" or begin by "test#", in the example of the previous function
 * will remove "test", "test#1" and "test#2"
 *
 * @array => array that you want to remove the key (by reference)
 * @name  => the key used in the array and as prefix of the entries of the array
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

/**
 * Fix Key
 *
 * This function returns the "real" part of the key removing the suffix added to
 * prevent collisions in the associative array, for the above example, if you request
 * the fix_key of the "test#2", the function will returns "test"
 *
 * @arg => the name of the key that you want to remove the suffix part (if exists)
 */
function fix_key($arg)
{
    $pos = strpos($arg, "#");
    if ($pos !== false) {
        $arg = substr($arg, 0, $pos);
    }
    return $arg;
}

/**
 * XML Files to Array
 *
 * This function allow to convert all XML files to an array, allow to use cache to
 * optimize repetitive calls of the same file
 *
 * As an special mention, this function internally implements the old xml_join feature
 * that allow to merge multiple files into one using the fix_key of the keys in the first
 * level as key to join.
 *
 * @files    => the files that you want to convert from xml to array
 * @usecache => if do you want to enable the cache feature
 */
function xmlfiles2array($files, $usecache = true)
{
    $result = [];
    foreach ($files as $file) {
        $array = xmlfile2array($file, $usecache);
        foreach ($array as $key => $val) {
            $key = fix_key($key);
            if (is_array($val)) {
                if (!isset($result[$key])) {
                    $result[$key] = [];
                }
                foreach ($val as $key2 => $val2) {
                    set_array($result[$key], $key2, $val2);
                }
            } else {
                set_array($result, $key, $val);
            }
        }
    }
    return $result;
}

/**
 * XML File to Array
 *
 * This function allow to convert a XML file to an array, allow to use cache to
 * optimize repetitive calls of the same file
 *
 * As an special mention, this function internally uses semaphores to prevent
 * multiple instances of the same execution with the same file, too uses a cache
 * management to optimize the usage
 *
 * @file     => the file that you want to convert from xml to array
 * @usecache => if do you want to enable the cache feature
 */
function xmlfile2array($file, $usecache = true)
{
    if (!file_exists($file)) {
        show_php_error(["xmlerror" => "File not found: $file"]);
    }
    if (!semaphore_acquire($file)) {
        show_php_error(["xmlerror" => "Could not acquire the semaphore"]);
    }
    if ($usecache) {
        $cache = get_cache_file($file, ".arr");
        if (cache_exists($cache, $file)) {
            $array = unserialize(file_get_contents($cache));
            if (isset($array["root"])) {
                semaphore_release($file);
                return $array["root"];
            }
        }
    }
    $xml = file_get_contents($file);
    $array = xml2array($xml, $file);
    if ($usecache) {
        file_put_contents($cache, serialize($array));
        chmod_protected($cache, 0666);
    }
    semaphore_release($file);
    return $array["root"];
}

/**
 * XML to Array
 *
 * This function allow to convert a XML string to an array
 *
 * @xml  => xml code to be converted to an array
 * @file => filename of the contents, only used when an errors occurs
 */
function xml2array($xml, $file = "")
{
    $data = xml2struct($xml, $file);
    $data = array_reverse($data);
    $array = struct2array($data, $file);
    return $array;
}

/**
 * XML to Struct
 *
 * This function is a helper of the xml2array function, the main purpose of this
 * function is to convert the xml string into a struct to be processed by the
 * struct2array function
 *
 * The motivation to use the xml_parse_into_struct function is that this function
 * is the more quick to parse xml files, after a lot of tests, the more quickly
 * execution is to use the xml_parse_into_struct, reverse the array and then
 * program a simple recursive function that convert a unidimensional array into
 * a tree
 *
 * At the begining of this function, we will try to detect the enconding of the
 * xml file, the main objective is to convert all xml to UTF-8 that is the default
 * enconding of SaltOS
 *
 * The returned value is the result of the xml_parse_into_struct function, that is
 * the key of this feature and this function
 *
 * @xml  => xml fragment that must be converted into struct
 * @file => the source filename, it is used only to generate error reports
 */
function xml2struct($xml, $file = "")
{
    // DETECT IF ENCODING ATTR IS FOUND
    $pos1 = strpos($xml, "<?xml");
    if ($pos1 !== false) {
        $pos2 = strpos($xml, "?>", $pos1);
        if ($pos2 !== false) {
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
    }
    $xml = getutf8($xml);
    // CONTINUE
    $parser = xml_parser_create();
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
    $array = [];
    $index = [];
    xml_parse_into_struct($parser, $xml, $array, $index);
    $code = xml_get_error_code($parser);
    if ($code) {
        $error = xml_error_string($code);
        $linea = xml_get_current_line_number($parser);
        $fila = xml_get_current_column_number($parser);
        if ($file == "") {
            show_php_error([
                "xmlerror" => "Error $code: $error (at line $linea,$fila)",
            ]);
        }
        $file = basename($file);
        show_php_error([
            "xmlerror" => "Error $code: $error (on file $file at line $linea,$fila)",
        ]);
    }
    xml_parser_free($parser);
    return $array;
}

/**
 * Struct to Array
 *
 * This function is the second part in the xml2array convertsion, here, the function
 * receives an unidimensional array with commands to open, close, and their respective
 * values and attributes, with this information, this function is able to generate a
 * tree with the xml converted to an array tree
 *
 * @data => the struct array, by reference
 * @file => the source filename, it is used only to generate error reports
 *
 * Notes:
 *
 * This function uses recursivity to accomplish the objetive, returns the portion
 * of xml between the open and close command, in each call, the data array passed
 * by reference will decrement in size because the array_pop removes the last element
 * of the array
 *
 * Remember that previously of call this function, the array is reversed, this is
 * because is more efficient to do a reverse and then pops instead of use directly
 * the array_shift to get the next element, the reason is that array_shift must to
 * reorder all keys of the resulted array and this add a very big cost if the xml
 * is big, this problem was detected in 2014 and was optimized by add the reverse
 * and the pop instead of only shift
 */
function struct2array(&$data, $file = "")
{
    $array = [];
    while ($linea = array_pop($data)) {
        $name = $linea["tag"];
        $type = $linea["type"];
        $value = "";
        if (isset($linea["value"])) {
            $value = $linea["value"];
        }
        $attr = [];
        if (isset($linea["attributes"])) {
            $attr = $linea["attributes"];
        }
        if ($type == "open") {
            // CASE 1 <some>
            $value = struct2array($data, $file);
            if (count($attr)) {
                $value = ["value" => $value, "#attr" => $attr];
            }
            set_array($array, $name, $value);
        } elseif ($type == "close") {
            // CASE 2 </some>
            return $array;
        } elseif ($type == "complete") {
            // CASE 3 <some/>
            // CASE 4 <some>some</some>
            if (count($attr)) {
                $value = ["value" => $value, "#attr" => $attr];
            }
            set_array($array, $name, $value);
        } elseif ($type == "cdata") {
            // NOTHING TO DO
        } else {
            if ($file == "") {
                show_php_error([
                    "xmlerror" => "Unknown tag type with name '</$name>' (at line $linea)",
                ]);
            }
            $file = basename($file);
            show_php_error([
                "xmlerror" => "Unknown tag type with name '</$name>' (on file $file at line $linea)",
            ]);
        }
    }
    return $array;
}

/**
 * Eval Attributes
 *
 * This function is very special in SaltOS, is part of the initial code an
 * is used by a lot of parts of the program, currently are using a simplified
 * version of the original function and have improvements that allow to return
 * arrays with attributes without evaluate and without causing an error, this
 * allow to define xml with attributes that can be used by other processes and
 * SaltOS only interpret three attributes
 *
 * @array => the array that contains a tree representation of the xml
 *
 * The three attributes are:
 *
 * @global => this attribute allow to SaltOS to prepare what variales must to
 * be global in the eval_protected call
 *
 * @eval => this attribute must be a boolean and allow to evaluate the value
 * of the node
 *
 * @ifeval => this attribute must contains an expression that must evaluate as
 * true or false, and allow to maintain or remove the entire node thas contains
 * the ifeval attribute, this is useful when you need a node in some conditions
 *
 * The great change between the eval_attr of the previous versions of SaltOS is
 * that this version only accepts three internal commands and the other
 * attributes can be maintained in order to be used by other processes
 * (internally or externally)
 */
function eval_attr($array)
{
    if (!is_array($array)) {
        return eval_attr(["inline" => $array])["inline"];
    }
    if (isset($array["value"]) && isset($array["#attr"])) {
        return eval_attr(["inline" => $array])["inline"];
    }
    $result = [];
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
                                    show_php_error(["xmlerror" => "Evaluation error: void expression"]);
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
                        case "require":
                            $val2 = explode(",", $val2);
                            foreach ($val2 as $file) {
                                if (!file_exists($file)) {
                                    show_php_error(["xmlerror" => "Require '$file' not found"]);
                                }
                                require_once $file;
                            }
                            unset($attr[$key2]);
                            break;
                    }
                }
                if (!$remove) {
                    $value  = eval_attr($value);
                    if (count($attr)) {
                        $result[$key] = ["value" => $value, "#attr" => $attr];
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

/**
 * Eval Bool
 *
 * This function returns a boolean depending on the input evaluation, the main idea
 * is to get an string, for example, and determine if must be considered true or false
 * otherwise will finish in an error
 *
 * The valid inputs are the strings one, zero, void, true, false, on, off, yes and no
 *
 * @arg => the value that do you want to evaluates as boolean
 */
function eval_bool($arg)
{
    $bools = [
        "1" => true,
        "0" => false,
        "" => false,
        "true" => true,
        "false" => false,
        "on" => true,
        "off" => false,
        "yes" => true,
        "no" => false,
    ];
    $bool = strtolower(strval($arg));
    if (isset($bools[$bool])) {
        return $bools[$bool];
    }
    show_php_error(["xmlerror" => "Unknown boolean value '$arg'"]);
}
