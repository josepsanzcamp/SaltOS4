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

// TODO: REVISAR ESTA FUNCION
function eval_protected($input, $global = "", $source = "")
{
    if (is_string($global) && $global != "") {
        foreach (explode(",", $global) as $var) {
            global $$var;
        }
    }
    if (is_array($global) && count($global)) {
        extract($global);
    }
    $output = eval("return $input;");
    return $output;
}

// TODO: REVISAR ESTA FUNCION
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
            xml_error("Unknown tag type with name '&lt;/$name&gt;'", $linea, "", $file);
        }
    }
    return $array;
}

// TODO: REVISAR ESTA FUNCION
function struct2array_include($input)
{
    if (!is_array($input)) {
        return $input;
    } elseif (isset($input["value"]) && isset($input["#attr"])) {
        $input["value"] = struct2array_include($input["value"]);
        return $input;
    } else {
        $array = array();
        foreach ($input as $name => $node) {
            if (isset($node["value"]) && isset($node["#attr"])) {
                $value = $node["value"];
                $attr = $node["#attr"];
                $include = 0;
                $replace = 0;
                foreach ($attr as $key => $val) {
                    $key = limpiar_key($key);
                    if ($key == "include") {
                        $value = xml2array($val);
                        $include = 1;
                    } elseif ($key == "replace") {
                        $replace = eval_bool($val);
                    }
                }
                if (isset($attr["path"]) && isset($attr["replace"])) {
                    $replace = 0;
                    $include = 0;
                }
                if (isset($attr["foreach"]) && isset($attr["as"]) && isset($attr["replace"])) {
                    $replace = 0;
                    $include = 0;
                }
                if (isset($attr["for"]) && isset($attr["from"]) && isset($attr["to"]) && isset($attr["replace"])) {
                    $replace = 0;
                    $include = 0;
                }
                if ($replace && !$include) {
                    xml_error("Attr 'replace' not allowed without attr 'include'");
                }
                if ($include) {
                    unset_array($attr, "include");
                }
                if ($replace) {
                    unset_array($attr, "replace");
                }
                if (count($attr)) {
                    if ($replace) {
                        if (is_array($value)) {
                            foreach ($value as $key => $val) {
                                if (is_array($val) && isset($val["value"]) && isset($val["#attr"])) {
                                    $value[$key]["#attr"] = $attr;
                                    foreach ($val["#attr"] as $key2 => $val2) {
                                        set_array($value[$key]["#attr"], $key2, $val2);
                                    }
                                } else {
                                    $value[$key] = array("value" => $val,"#attr" => $attr);
                                }
                            }
                        }
                    } else {
                        $value = array("value" => $value,"#attr" => $attr);
                    }
                }
                if ($replace && is_array($value)) {
                    foreach ($value as $key => $val) {
                        set_array($array, $key, struct2array_include($val));
                    }
                } else {
                    set_array($array, $name, struct2array_include($value));
                }
            } else {
                set_array($array, $name, struct2array_include($node));
            }
        }
        return $array;
    }
}

// TODO: REVISAR ESTA FUNCION
function struct2array_path($input)
{
    if (!is_array($input)) {
        return $input;
    } elseif (isset($input["value"]) && isset($input["#attr"])) {
        $input["value"] = struct2array_path($input["value"]);
        return $input;
    } else {
        $array = array();
        foreach ($input as $name => $node) {
            if (isset($node["value"]) && isset($node["#attr"])) {
                $value = $node["value"];
                $attr = $node["#attr"];
                $path = "";
                $action = "";
                foreach ($attr as $key => $val) {
                    $key = limpiar_key($key);
                    if ($key == "path") {
                        $path = $val;
                    } elseif (
                        in_array(
                            $key,
                            array("before","after","replace","append","add","prepend","remove","delete")
                        )
                    ) {
                        if ($action != "") {
                            xml_error("Detected '$action' and '$key' attr in the same node");
                        }
                        $action = $key;
                    }
                }
                // if (isset($attr["foreach"]) && isset($attr["as"]) && isset($attr["replace"])) {
                    // $action = "";
                // }
                // if (isset($attr["for"]) && isset($attr["from"]) && isset($attr["to"]) && isset($attr["replace"])) {
                    // $action = "";
                // }
                if ($path && !$action) {
                    xml_error("Detected 'path' attr without before, after, replace, append, add, prepend, remove or delete' attr");
                }
                if ($action && !$path) {
                    xml_error("Detected '$action' attr without 'path' attr");
                }
                if ($path) {
                    unset_array($attr, "path");
                }
                if ($action) {
                    unset_array($attr, $action);
                }
                if ($path) {
                    $array = __set_array_recursive($array, $path, struct2array_path($value), $action);
                }
                if (count($attr)) {
                    $value = array("value" => $value,"#attr" => $attr);
                }
                if (!$path) {
                    set_array($array, $name, struct2array_path($value));
                }
            } else {
                set_array($array, $name, struct2array_path($node));
            }
        }
        return $array;
    }
}

// TODO: REVISAR ESTA FUNCION
function __set_array_recursive($array, $keys, $value, $type)
{
    if (!is_array($keys)) {
        $keys = explode("/", $keys);
    }
    $key0 = array_shift($keys);
    // RESOLVE NODE USING XPATH SYNTAX
    $path = explode("[", str_replace("]", "", $key0));
    $count = count($path);
    if ($count > 1) {
        for ($i = 1; $i < $count; $i++) {
            $path[$i] = explode("=", $path[$i], 2);
            if (!isset($path[$i][1])) {
                $path[$i][1] = "";
            }
        }
        $key = array();
        foreach ($array as $key2 => $val2) {
            $valid = 1;
            $hasattr = (isset($val2["value"]) && isset($val2["#attr"]));
            if (!in_array($path[0], array("","*",limpiar_key($key2)))) {
                $valid = 0;
            }
            for ($i = 1; $i < $count && $valid; $i++) {
                if ($hasattr) {
                    if (!isset($val2["value"][$path[$i][0]])) {
                        $valid = 0;
                    } elseif ($val2["value"][$path[$i][0]] != $path[$i][1]) {
                        $valid = 0;
                    }
                } else {
                    if (!isset($val2[$path[$i][0]])) {
                        $valid = 0;
                    } elseif ($val2[$path[$i][0]] != $path[$i][1]) {
                        $valid = 0;
                    }
                }
            }
            if ($valid) {
                $key[] = $key2;
            }
        }
    } else {
        $key = array($key0);
    }
    // CONTINUE
    if (count($keys) > 0) {
        foreach ($key as $key2) {
            if (!isset($array[$key2])) {
                xml_error("Undefined node: $key2");
            }
            $array[$key2] = __set_array_recursive($array[$key2], $keys, $value, $type);
        }
    } else {
        $temp = array();
        $hasattr = (isset($array["value"]) && isset($array["#attr"]));
        $array_value = $hasattr ? $array["value"] : $array;
        $array_attr = $hasattr ? $array["#attr"] : array();
        foreach ($array_value as $key2 => $val2) {
            if (in_array($key2, $key)) {
                switch ($type) {
                    case "before":
                        foreach ($value as $key3 => $val3) {
                            set_array($temp, limpiar_key($key3), $val3);
                        }
                        set_array($temp, limpiar_key($key2), $val2);
                        break;
                    case "after":
                        set_array($temp, limpiar_key($key2), $val2);
                        foreach ($value as $key3 => $val3) {
                            set_array($temp, limpiar_key($key3), $val3);
                        }
                        break;
                    case "replace":
                        foreach ($value as $key3 => $val3) {
                            set_array($temp, limpiar_key($key3), $val3);
                        }
                        break;
                    case "append":
                    case "add":
                        $hasattr = (isset($val2["value"]) && isset($val2["#attr"]));
                        foreach ($value as $key3 => $val3) {
                            if ($hasattr) {
                                if (!is_array($val2["value"])) {
                                    xml_error("Can not '$type' the node '$key3' to the node '$key2'");
                                }
                                set_array($val2["value"], limpiar_key($key3), $val3);
                            } else {
                                if (!is_array($val2)) {
                                    xml_error("Can not '$type' the node '$key3' to the node '$key2'");
                                }
                                set_array($val2, limpiar_key($key3), $val3);
                            }
                        }
                        set_array($temp, limpiar_key($key2), $val2);
                        break;
                    case "prepend":
                        $hasattr = (isset($val2["value"]) && isset($val2["#attr"]));
                        foreach ($value as $key3 => $val3) {
                            if ($hasattr) {
                                if (!is_array($val2["value"])) {
                                    xml_error("Can not '$type' the node '$key3' to the node '$key2'");
                                }
                                $val2["value"] = array_reverse($val2["value"]);
                                set_array($val2["value"], limpiar_key($key3), $val3);
                                $val2["value"] = array_reverse($val2["value"]);
                            } else {
                                if (!is_array($val2)) {
                                    xml_error("Can not '$type' the node '$key3' to the node '$key2'");
                                }
                                $val2 = array_reverse($val2);
                                set_array($val2, limpiar_key($key3), $val3);
                                $val2 = array_reverse($val2);
                            }
                        }
                        set_array($temp, limpiar_key($key2), $val2);
                        break;
                    case "remove":
                    case "delete":
                        // NOTHING TO DO
                        break;
                    default:
                        xml_error("Unknown type '$type' in __set_array_recursive");
                        break;
                }
            } else {
                set_array($temp, limpiar_key($key2), $val2);
            }
        }
        $array = count($array_attr) ? array("value" => $temp,"#attr" => $array_attr) : $temp;
    }
    return $array;
}

// TODO: REVISAR ESTA FUNCION
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

// TODO: REVISAR ESTA FUNCION
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

// TODO: REVISAR ESTA FUNCION
function limpiar_key($arg)
{
    if (is_array($arg)) {
        foreach ($arg as $key => $val) {
            $arg[$key] = limpiar_key($val);
        }
        return $arg;
    }
    $pos = strpos($arg, "#");
    if ($pos !== false) {
        $arg = substr($arg, 0, $pos);
    }
    return $arg;
}

//~ // TODO: REVISAR ESTA FUNCION
//~ function eval_files()
//~ {
    //~ foreach ($_FILES as $key => $val) {
        //~ if (isset($val["tmp_name"]) && $val["tmp_name"] != "" && file_exists($val["tmp_name"])) {
            //~ if (!isset($val["name"])) {
                //~ $val["name"] = basename($val["tmp_name"]);
            //~ }
            //~ $val["file"] = time() . "_" . get_unique_id_md5() . "_" . encode_bad_chars_file($val["name"]);
            //~ if (!isset($val["size"])) {
                //~ $val["size"] = filesize($val["tmp_name"]);
            //~ }
            //~ if (!isset($val["type"])) {
                //~ $val["type"] = saltos_content_type($val["tmp_name"]);
            //~ }
            //~ $val["hash"] = md5_file($val["tmp_name"]);
            //~ // SECURITY ISSUE
            //~ if (substr($val["file"], -4, 4) == ".php") {
                //~ $val["file"] = substr($val["file"], 0, -4) . ".dat";
            //~ }
            //~ // FOLDER ISSUE
            //~ $val["file"] = get_param("page", "unknown") . "/" . $val["file"];
            //~ // CONTINUE
            //~ set_param($key, $val["name"]);
            //~ set_param($key . "_file", $val["file"]);
            //~ set_param($key . "_size", $val["size"]);
            //~ set_param($key . "_type", $val["type"]);
            //~ set_param($key . "_hash", $val["hash"]);
            //~ set_param($key . "_temp", $val["tmp_name"]);
        //~ } else {
            //~ if (isset($val["name"]) && $val["name"] != "") {
                //~ session_error(LANG("fileuploaderror") . $val["name"]);
            //~ }
            //~ if (isset($val["error"]) && $val["error"] != "") {
                //~ session_error(
                    //~ LANG("fileuploaderror") . upload_error2string($val["error"]) . " (code " . $val["error"] . ")"
                //~ );
            //~ }
        //~ }
    //~ }
//~ }

// TODO: REVISAR ESTA FUNCION
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

// TODO: REVISAR ESTA FUNCION
function xml2array($file, $usecache = true)
{
    static $depend = array();
    if (!file_exists($file)) {
        xml_error("File not found: $file");
    }
    if (!semaphore_acquire($file)) {
        xml_error("Could not acquire the semaphore");
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
    $array = struct2array_include($array);
    if (detect_recursion(__FUNCTION__) == 1) {
        $array = struct2array_path($array);
    }
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

// TODO: REVISAR ESTA FUNCION
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
    //~ xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,check_xml_option_skip_white());
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
    $array = array();
    $index = array();
    // TO PREVENT A MEMORY LEAK IN LIBXML
    if (strlen($xml) > 1048576) {
        __xml_parse_into_struct_chunk($parser, $xml, $array, $index);
    } else {
        xml_parse_into_struct($parser, $xml, $array, $index);
    }
    $code = xml_get_error_code($parser);
    if ($code) {
        $error = xml_error_string($code);
        $linea = xml_get_current_line_number($parser);
        $fila = xml_get_current_column_number($parser);
        xml_error("Error " . $code . ": " . $error, "", $linea . "," . $fila, $file);
    }
    xml_parser_free($parser);
    return $array;
}

function __xml_parse_into_struct_chunk($parser, $data, &$values, &$index = "")
{
    __xml_parse_into_struct_array("values", "set", $values);
    __xml_parse_into_struct_array("index", "set", $index);
    xml_set_element_handler($parser, "__xml_parse_into_struct_start", "__xml_parse_into_struct_end");
    xml_set_character_data_handler($parser, "__xml_parse_into_struct_cdata");
    $data = str_split($data, 1048576);
    while ($part = array_shift($data)) {
        $estado = xml_parse($parser, $part, count($data) == 0);
        if (!$estado) {
            break;
        }
    }
    $values = __xml_parse_into_struct_array("values", "get");
    $index = __xml_parse_into_struct_array("index", "get");
    return $estado;
}

function __xml_parse_into_struct_start($parser, $tag, $attr)
{
    __xml_parse_into_struct_array("index", "push", $tag);
    $level = __xml_parse_into_struct_array("index", "count");
    $temp = array(
        "tag" => $tag,
        "type" => "open",
        "level" => $level,
    );
    if (count($attr)) {
        $temp["attributes"] = $attr;
    }
    __xml_parse_into_struct_array("values", "push", $temp);
}

function __xml_parse_into_struct_end($parser, $tag)
{
    $level = __xml_parse_into_struct_array("index", "count");
    $temp = __xml_parse_into_struct_array("values", "pop");
    if ($temp["tag"] == $tag && $temp["type"] == "open") {
        $temp["type"] = "complete";
        __xml_parse_into_struct_array("values", "push", $temp);
    } else {
        __xml_parse_into_struct_array("values", "push", $temp);
        $temp = array(
            "tag" => $tag,
            "type" => "close",
            "level" => $level,
        );
        __xml_parse_into_struct_array("values", "push", $temp);
    }
    __xml_parse_into_struct_array("index", "pop");
}

function __xml_parse_into_struct_cdata($parser, $data)
{
    $tag = __xml_parse_into_struct_array("index", "end");
    $level = __xml_parse_into_struct_array("index", "count");
    $temp = __xml_parse_into_struct_array("values", "pop");
    if ($temp["tag"] == $tag && $temp["level"] == $level) {
        if (isset($temp["value"])) {
            $temp["value"] .= $data;
        } else {
            $temp["value"] = $data;
        }
        __xml_parse_into_struct_array("values", "push", $temp);
    } else {
        __xml_parse_into_struct_array("values", "push", $temp);
        $temp = array(
            "tag" => $tag,
            "value" => $data,
            "type" => "cdata",
            "level" => $level,
        );
        __xml_parse_into_struct_array("values", "push", $temp);
    }
}

function __xml_parse_into_struct_array($key, $op, $val = "")
{
    static $cache = array();
    if (!isset($cache[$key])) {
        $cache[$key] = array();
    }
    switch ($op) {
        case "set":
            $cache[$key] = $val;
            break;
        case "get":
            return $cache[$key];
        case "count":
            return count($cache[$key]);
        case "end":
            return end($cache[$key]);
        case "push":
            array_push($cache[$key], $val);
            break;
        case "pop":
            return array_pop($cache[$key]);
            break;
    }
}

//~ function check_xml_option_skip_white() {
    //~ static $xml_option_skip_white=null;
    //~ if($xml_option_skip_white===null) {
        //~ $test="1\n2";
        //~ $xml="<root>{$test}</root>";
        //~ $parser=xml_parser_create();
        //~ xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
        //~ xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1);
        //~ xml_parser_set_option($parser,XML_OPTION_TARGET_ENCODING,"UTF-8");
        //~ $array=array();
        //~ $index=array();
        //~ xml_parse_into_struct($parser,$xml,$array,$index);
        //~ xml_parser_free($parser);
        //~ $xml_option_skip_white=($array[0]["value"]==$test)?1:0;
    //~ }
    //~ return $xml_option_skip_white;
//~ }

// TODO: REVISAR ESTA FUNCION
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
                $stack = array();
                $global = "";
                $value = $val["value"];
                $attr = $val["#attr"];
                $count = 0;
                foreach ($attr as $key2 => $val2) {
                    $key2 = limpiar_key($key2);
                    switch ($key2) {
                        case "global":
                            $global = $val2;
                            foreach (explode(",", $global) as $var) {
                                global $$var;
                            }
                            break;
                        case "eval":
                            if (eval_bool($val2)) {
                                if (!$value) {
                                    xml_error("Evaluation error: void expression");
                                }
                                $value = eval_protected($value, $global);
                            }
                            break;
                        case "ifeval":
                            $val2 = eval_protected($val2, $global);
                            if (!$val2) {
                                $stack["remove"] = 1;
                            }
                            break;
                        case "ifpreeval":
                            $val2 = eval_protected($val2, $global);
                            if (!$val2) {
                                $stack["cancel"] = 1;
                            }
                            break;
                        case "require":
                            $val2 = explode(",", $val2);
                            foreach ($val2 as $file) {
                                if (!file_exists($file)) {
                                    xml_error("Require '$file' not found");
                                }
                                require_once $file;
                            }
                            break;
                        case "replace":
                            if (eval_bool($val2)) {
                                $stack["replace"] = 1;
                            }
                            break;
                        default:
                            xml_error("Unknown attr '$key2' with value '$val2'");
                    }
                    $count++;
                    if (isset($stack["cancel"]) || isset($stack["remove"])) {
                        break;
                    }
                }
                if (isset($stack["cancel"])) {
                    $result[$key] = $val;
                } elseif (isset($stack["remove"])) {
                    // NOTHING TO DO
                } elseif (isset($stack["replace"])) {
                    foreach ($value as $v) {
                        foreach ($v as $key2 => $val2) {
                            set_array($result, $key2, $val2);
                        }
                    }
                } elseif (!is_array($value)) {
                    $result[$key] = $value;
                } elseif (!is_array($val["value"])) {
                    foreach ($value as $v) {
                        set_array($result, $key, $v);
                    }
                } else {
                    $result[$key] = eval_attr($value);
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

// TODO: REVISAR ESTA FUNCION
function eval_bool($arg)
{
    static $bools = array(
        "1" => 1, // FOR 1 OR TRUE
        "0" => 0, // FOR 0
        "" => 0, // FOR FALSE
        "true" => 1,
        "false" => 0,
        "on" => 1,
        "off" => 0,
        "yes" => 1,
        "no" => 0
    );
    $bool = strtolower($arg);
    if (isset($bools[$bool])) {
        return $bools[$bool];
    }
    xml_error("Unknown boolean value '$arg'");
}

// TODO: REVISAR ESTA FUNCION
function xml_error($error, $source = "", $count = "", $file = "")
{
    $array = array();
    $array["xmlerror"] = $error;
    if ($count != "" && $file == "") {
        $array["xmlerror"] .= " (at line $count)";
    }
    if ($count == "" && $file != "") {
        $array["xmlerror"] .= " (on file " . basename($file) . ")";
    }
    if ($count != "" && $file != "") {
        $array["xmlerror"] .= " (on file " . basename($file) . " at line $count)";
    }
    if (is_array($source)) {
        $array["source"] = htmlentities(sprintr($source), ENT_COMPAT, "UTF-8");
    } elseif ($source != "") {
        $array["source"] = htmlentities($source, ENT_COMPAT, "UTF-8");
    }
    show_php_error($array);
}
