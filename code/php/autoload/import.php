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

/**
 * Import File
 *
 * This function is intended to import data in the supported formats
 *
 * @data => contents used as data instead of file
 * @file => local filename used to load the data
 * @type => can be xml, csv, xls, bytes, edi or json
 * @sep => separator char used only by csv format
 * @sheet => sheet that must to be read
 * @map => map used as dictionary for each field, pos and length
 * @offset => the offset added to the start position in each map field
 * @nomb => boolean to disable or enable the multibyte support
 * @novoid => boolean to enable or disable the removevoid feature
 * @prefn => function executed between the load and the tree construction
 * @notree => boolean to enable or disable the array2tree feature
 * @nodes => an array with the fields that define each nodes used in the tree construction
 * @nohead => if the first row doesn't contains the header of the data, put this field to one
 * @noletter => if you want to use numeric index instead of excel index, put this field to one
 * @postfn => function executed after the tree construction
 *
 * This function returns an array with the loaded data from file
 * Can return a matrix or tree, depending the nodes parameter
 */
function import_file($args)
{
    // Check parameters
    if (isset($args["data"])) {
        $args["file"] = get_cache_file($args["data"], "tmp");
        if (!file_exists($args["file"])) {
            file_put_contents($args["file"], $args["data"]);
        }
    }
    if (!isset($args["file"])) {
        show_php_error(array("phperror" => "Unknown file"));
    }
    if (!isset($args["type"])) {
        show_php_error(array("phperror" => "Unknown type"));
    }
    if (!isset($args["sep"])) {
        $args["sep"] = ";";
    }
    if (!isset($args["sheet"])) {
        $args["sheet"] = 0;
    }
    if (!isset($args["map"])) {
        $args["map"] = "";
    }
    if (!isset($args["offset"])) {
        $args["offset"] = 0;
    }
    if (!isset($args["nomb"])) {
        $args["nomb"] = 0;
    }
    if (!isset($args["novoid"])) {
        $args["novoid"] = 0;
    }
    if (!isset($args["prefn"])) {
        $args["prefn"] = "";
    }
    if (!isset($args["notree"])) {
        $args["notree"] = 0;
    }
    if (!isset($args["nodes"])) {
        $args["nodes"] = array();
    }
    if (!isset($args["nohead"])) {
        $args["nohead"] = 0;
    }
    if (!isset($args["noletter"])) {
        $args["noletter"] = 0;
    }
    if (!isset($args["postfn"])) {
        $args["postfn"] = "";
    }
    if (!file_exists($args["file"])) {
        return "Error: File '{$args["file"]}' not found";
    }
    // Continue
    switch ($args["type"]) {
        case "application/xml":
        case "text/xml":
        case "xml":
            $array = __import_xml2array($args["file"]);
            break;
        case "text/plain":
        case "text/csv":
        case "csv":
            $array = __import_csv2array($args["file"], $args["sep"]);
            break;
        case "application/wps-office.xls":
        case "application/vnd.ms-excel":
        case "application/excel":
        case "excel":
        case "xlsx":
        case "xls":
            $array = __import_xls2array($args["file"], $args["sheet"]);
            break;
        case "bytes":
            $array = __import_bytes2array($args["file"], $args["map"], $args["offset"], $args["nomb"]);
            break;
        case "edi":
            $array = __import_edi2array($args["file"]);
            break;
        case "application/json":
        case "text/json":
        case "json":
            $array = __import_json2array($args["file"]);
            break;
        default:
            return "Error: Unknown type '{$args["type"]}' for file '{$args["file"]}'";
    }
    if (!is_array($array)) {
        return $array;
    }
    if (!$args["novoid"]) {
        $array = __import_removevoid($array);
        if (!is_array($array)) {
            return $array;
        }
    }
    if ($args["prefn"]) {
        $array = $args["prefn"]($array,$args);
        if (!is_array($array)) {
            return $array;
        }
    }
    if (!$args["notree"]) {
        $array = __import_array2tree($array, $args["nodes"], $args["nohead"], $args["noletter"]);
        if (!is_array($array)) {
            return $array;
        }
    }
    if ($args["postfn"]) {
        $array = $args["postfn"]($array,$args);
        if (!is_array($array)) {
            return $array;
        }
    }
    return $array;
}

/**
 * UTF8 BOM helper
 *
 * This function remove the bom header of the string
 *
 * @data => the data that must to be checked
 *
 * Returns the data without the bom characters
 */
function __import_utf8bom($data)
{
    if (substr($data, 0, 3) == "\xef\xbb\xbf") {
        $data = substr($data, 3);
    }
    return $data;
}

/**
 * XML to Array
 *
 * This function convert an xml into an array
 *
 * @file => the file that contains the xml
 *
 * Returns an array with the contents of the xml
 */
function __import_xml2array($file)
{
    $xml = file_get_contents($file);
    $xml = __import_utf8bom($xml);
    capture_next_error();
    $data = xml2struct($xml);
    $error = get_clear_error();
    if ($error != "") {
        $temp = parse_error2array($error);
        if (isset($temp[1])) {
            return $temp[1];
        }
        return $error;
    }
    $data = array_reverse($data);
    $array = __import_struct2array($data);
    return $array;
}

/**
 * Struct to Array helper
 *
 * This function is a helper of the __import_xml2array
 *
 * @data => an array with all nodes of the xml file
 *
 * Returns an array with the correct structure that matches the xml structure
 */
function __import_struct2array(&$data)
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
            // caso 1 <algo>
            $value = __import_struct2array($data);
            if (count($attr)) {
                $value = array("value" => $value,"#attr" => $attr);
            }
            set_array($array, $name, $value);
        } elseif ($type == "close") {
            // caso 2 </algo>
            return $array;
        } elseif ($type == "complete") {
            // caso 3 <algo/>
            // caso 4 <algo>algo</algo>
            if (count($attr)) {
                $value = array("value" => $value,"#attr" => $attr);
            }
            set_array($array, $name, $value);
        } elseif ($type == "cdata") {
            // Nothing to do
        } else {
            xml_error("Unknown tag type with name '&lt;/$name&gt;'", $linea);
        }
    }
    return $array;
}

/**
 * Get Node helper
 *
 * This function is a helper used to get a node in a xml structure
 *
 * @path => a path of the desired node
 * @array => the array with nodes of the xml structure
 *
 * Returns the contents of the node of the specified path
 */
function __import_getnode($path, $array)
{
    if (!is_array($path)) {
        $path = explode("/", $path);
    }
    $elem = array_shift($path);
    if (!is_array($array) || !isset($array[$elem])) {
        return null;
    }
    if (count($path) == 0) {
        return $array[$elem];
    }
    return __import_getnode($path, __import_getvalue($array[$elem]));
}

/**
 * Get Value helper
 *
 * This function is a helper used to get a value if exists of a node structure
 *
 * @array => an array
 *
 * Retusn the value if exists, otherwise the same input
 */
function __import_getvalue($array)
{
    return (is_array($array) && isset($array["value"]) && isset($array["#attr"])) ? $array["value"] : $array;
}

/**
 * Get Attr helper
 *
 * This function is a helper used to get a attr element if exists of a node structure
 *
 * @elem => a string representing an element
 * @array => an array containing the node
 *
 * Returns the attr if exists, otherwise null
 */
function __import_getattr($elem, $array)
{
    if (!is_array($array) || !isset($array["#attr"]) || !is_array($array["#attr"]) || !isset($array["#attr"][$elem])) {
        return null;
    }
    return $array["#attr"][$elem];
}

/**
 * Set Node helper
 *
 * This function is used to set data into a xml structure
 *
 * @path => the desired path where do you want to put the data,
 * @array => the array with the xml structure
 * @value => the value that do you want to put
 *
 * Returns true if the function can set the value, false otherwise
 */
function __import_setnode($path, &$array, $value)
{
    if (!is_array($path)) {
        $path = explode("/", $path);
    }
    $elem = array_shift($path);
    if (!is_array($array) || !isset($array[$elem])) {
        return false;
    }
    if (count($path) == 0) {
        $array[$elem] = $value;
        return true;
    }
    if (is_array($array[$elem]) && isset($array[$elem]["value"]) && isset($array[$elem]["#attr"])) {
        return __import_setnode($path, $array[$elem]["value"], $value);
    } else {
        return __import_setnode($path, $array[$elem], $value);
    }
}

/**
 * Del Node helper
 *
 * This function is used to remove data of the xml structure
 *
 * @path => the desired path where do you want to remove
 * @array => the array with the xml structure
 *
 * Returns true if the function can remove the path, false otherwise
 */
function __import_delnode($path, &$array)
{
    if (!is_array($path)) {
        $path = explode("/", $path);
    }
    $elem = array_shift($path);
    if (!is_array($array) || !isset($array[$elem])) {
        return false;
    }
    if (count($path) == 0) {
        unset($array[$elem]);
        return true;
    }
    if (is_array($array[$elem]) && isset($array[$elem]["value"]) && isset($array[$elem]["#attr"])) {
        return __import_delnode($path, $array[$elem]["value"]);
    } else {
        return __import_delnode($path, $array[$elem]);
    }
}

/**
 * Add Node helper
 *
 * This function is used to add data into a xml structure
 *
 * @path => the desired path where do you want to add the data
 * @array => the array with the xml structure
 * @value => the value that do you want to add
 *
 * true if the function can add the data, false otherwise
 */
function __import_addnode($path, &$array, $value)
{
    if (!is_array($path)) {
        $path = explode("/", $path);
    }
    $elem = array_shift($path);
    if (count($path) == 0) {
        set_array($array, $elem, $value);
        return true;
    }
    if (!is_array($array) || !isset($array[$elem])) {
        return false;
    }
    if (is_array($array[$elem]) && isset($array[$elem]["value"]) && isset($array[$elem]["#attr"])) {
        return __import_addnode($path, $array[$elem]["value"], $value);
    } else {
        return __import_addnode($path, $array[$elem], $value);
    }
}

/**
 * Special Chars helper
 *
 * This function is a helper used by the csv2array function
 *
 * @arg => a string or array
 *
 * Returns the input with the expected replacements
 */
function __import_specialchars($arg)
{
    $orig = array("\\t","\\r","\\n");
    $dest = array("\t","\r","\n");
    return str_replace($orig, $dest, $arg);
}

/**
 * CSV to Array helper
 *
 * This function is a helper of the __import_xml2array
 *
 * @file => the filename and the sheet that do you want to retrieve
 * @sep => the separator field used in the csv file
 *
 * Returns a matrix with the contents
 */
function __import_csv2array($file, $sep)
{
    $sep = __import_specialchars($sep);
    $fd = fopen($file, "r");
    $array = array();
    while ($row = fgetcsv($fd, 0, $sep)) {
        foreach ($row as $key => $val) {
            $row[$key] = getutf8($val);
        }
        $array[] = $row;
    }
    fclose($fd);
    if (isset($array[0][0])) {
        $array[0][0] = __import_utf8bom($array[0][0]);
    }
    return $array;
}

/**
 * XLS to Array helper
 *
 * This fuction can convert an excel file into a matrix structure, it has some additional features as:
 * - If the file exceds the 1Mbyte and the server has the xlsx2csv executable, it tries to convert the xslx
 *   to an excel to use less memory
 * - Do some internals trics to solve some knowed issues
 *
 * @file => the filename and the sheet that do you want to retrieve
 * @sheet => the second parameter can be a number or a sheet name
 *
 * Returns a matrix with the contents
 */
function __import_xls2array($file, $sheet)
{
    require_once "lib/phpspreadsheet/vendor/autoload.php";
    $objReader = PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file);
    // Check the sheet param
    if (!method_exists($objReader, "listWorksheetNames")) {
        return "Error: Sheets not found in the file";
    }
    // libxml_use_internal_errors is a trick to prevent the simplexml_load_string error when gets binary data
    libxml_use_internal_errors(true); // Trick
    $sheets = $objReader->listWorksheetNames($file);
    libxml_use_internal_errors(false); // Trick
    if (is_numeric($sheet)) {
        if (!isset($sheets[$sheet])) {
            return "Error: Sheet number '{$sheet}' not found";
        }
    } else {
        foreach ($sheets as $key => $val) {
            if ($sheet == $val) {
                $sheet = $key;
                break;
            }
        }
        if (!is_numeric($sheet)) {
            return "Error: Sheet named '{$sheet}' not found";
        }
    }
    // Trick for a big files
    if (filesize($file) > 1048576 && check_commands(get_default("commands/xlsx2csv"), 60)) { // filesize > 1Mb
        $csv = get_cache_file($file, "csv");
        if (!file_exists($csv)) {
            $xlsx = get_cache_file($file, "xlsx");
            $fix = (dirname(realpath($file)) != dirname($xlsx));
            if ($fix) {
                symlink($file, $xlsx);
            }
            if (!$fix) {
                $xlsx = $file;
            }
            ob_passthru(str_replace(
                array("__DIR__","__INPUT__"),
                array(dirname($xlsx),basename($xlsx)),
                get_default("commands/__xlsx2csv__")
            ));
            if ($fix) {
                unlink($xlsx);
            }
            foreach ($sheets as $key => $val) {
                $temp = $xlsx . "." . $val . ".csv";
                if (file_exists($temp)) {
                    if ($key == $sheet) {
                        rename($temp, $csv);
                    } else {
                        unlink($xlsx . "." . $val . ".csv");
                    }
                }
            }
        }
        if (file_exists($csv)) {
            unset($objReader);
            $array = __import_csv2array($csv, ",");
            return $array;
        }
    }
    // Continue
    $objPHPExcel = $objReader->load($file);
    $objSheet = $objPHPExcel->getSheet($sheet);
    // Detect cols and rows with data
    $cells = $objSheet->getCoordinates(true);
    $cols = array();
    $rows = array();
    foreach ($cells as $cell) {
        list($col,$row) = __import_cell2colrow($cell);
        $cols[$col] = __import_name2col($col);
        $rows[$row] = $row;
    }
    // Important trick: to order the cols, we needed to convert it into numbers before to do the real order,
    // and when the list has the correct order, then we can convert it to the original letters
    sort($cols, SORT_NUMERIC);
    sort($rows, SORT_NUMERIC);
    foreach ($cols as $key => $val) {
        $cols[$key] = __import_col2name($val);
    }
    // Read data
    $array = array();
    foreach ($rows as $row) {
        $temp = array();
        foreach ($cols as $col) {
            $cell = $objSheet->getCell($col . $row);
            if ($cell->isFormula()) {
                $temp2 = $cell->getOldCalculatedValue();
            } elseif (PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
                //~ $temp2=$cell->getValue();
                //~ $temp2=PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($temp2);
                //~ $temp2=date("Y-m-d",$temp2);
                $cell->getStyle()->getNumberFormat()->setFormatCode("YYYY-MM-DD");
                $temp2 = $cell->getFormattedValue();
            } else {
                $temp2 = $cell->getFormattedValue();
            }
            $temp[] = $temp2;
        }
        $array[] = $temp;
    }
    // Release memory
    unset($objReader);
    unset($objPHPExcel);
    unset($objSheet);
    // Continue
    return $array;
}

/**
 * Bytes to Array helper
 *
 * This function can read files as blocks of bytes, they can use a map, can specify
 * an offset and can be used using multibyte if it is needed
 *
 * @file => local filename used to load the data
 * @map => map used as dictionary for each field, pos and length
 * @offset => the offset added to the start position in each map field
 * @nomb => boolean to disable or enable the multibyte support
 *
 * Returns a matrix with the contents
 *
 * Notes:
 *
 * The map must be an string of the follow form:
 *                     +--------------+
 *                     | field1;0;10  |
 *                     | field2;10;20 |
 *                     | field3;20;40 |
 *                     +--------------+
 */
function __import_bytes2array($file, $map, $offset, $nomb)
{
    if (!is_array($map)) {
        $map = trim($map);
        $map = explode("\n", $map);
        foreach ($map as $key => $val) {
            $val = trim($val);
            $val = explode(";", $val);
            $map[$key] = $val;
        }
    }
    $lines = file($file, FILE_IGNORE_NEW_LINES);
    if (isset($lines[0])) {
        $lines[0] = __import_utf8bom($lines[0]);
    }
    $array = array();
    $row = array();
    foreach ($map as $map0) {
        $row[] = $map0[0];
    }
    $array[] = $row;
    foreach ($lines as $line) {
        $line = getutf8($line);
        $row = array();
        foreach ($map as $map0) {
            if ($nomb) {
                $temp = substr($line, $map0[1] + $offset, $map0[2]);
            } else {
                $temp = mb_substr($line, $map0[1] + $offset, $map0[2]);
            }
            $row[] = trim($temp);
        }
        $array[] = $row;
    }
    return $array;
}

/**
 * Edit to Array helper
 *
 * This fuction can convert an excel file into a tree structure
 *
 * @file => local filename used to load the data
 */
function __import_edi2array($file)
{
    require_once "lib/edifact/vendor/autoload.php";
    $parser = new EDI\Parser();
    $parser->load($file);
    $array = $parser->get();
    return $array;
}

/**
 * JSON to Array helper
 *
 * This fuction can convert an excel file into a tree structure
 *
 * @file => local filename used to load the data
 */
function __import_json2array($file)
{
    $array = json_decode(file_get_contents($file), true);
    if (!is_array($array)) {
        $code = json_last_error();
        $msg = json_last_error_msg();
        return "Error: {$msg} ({$code})";
    }
    return $array;
}

/**
 * Check Real Matrix helper
 *
 * This function checks that the argument is a matrix, to do this, checks
 * that the argument is an array, that all keys are numeric and that all
 * entries of the main array is another array, and for each another array,
 * checks that the keys are numeric and that all values are non arrays
 *
 * @array => the array to check
 */
function __import_check_real_matrix($array)
{
    foreach ($array as $key => $val) {
        if (!is_numeric($key)) {
            return false;
        } elseif (!is_array($val)) {
            return false;
        } else {
            foreach ($val as $key2 => $val2) {
                if (!is_numeric($key2)) {
                    return false;
                } elseif (is_array($val2)) {
                    return false;
                }
            }
        }
    }
    return true;
}

/**
 * Remove Void helper
 *
 * This function is able to remove an entire row or column if it is void
 *
 * @array => the array to fix
 */
function __import_removevoid($array)
{
    // Initial checks
    if (!is_array($array)) {
        return $array;
    }
    if (!count($array)) {
        return $array;
    }
    if (!__import_check_real_matrix($array)) {
        return $array;
    }
    // Continue
    $count_rows = count($array);
    $rows = array_fill(0, $count_rows, 0);
    $count_cols = 0;
    foreach ($array as $val) {
        $count_cols = max($count_cols, count($val));
    }
    $cols = array_fill(0, $count_cols, 0);
    foreach ($array as $key => $val) {
        foreach ($val as $key2 => $val2) {
            if ($val2 != "") {
                $rows[$key]++;
                $cols[$key2]++;
            }
        }
    }
    $rows = array_keys(array_intersect($rows, array(0)));
    $cols = array_keys(array_intersect($cols, array(0)));
    foreach ($rows as $val) {
        unset($array[$val]);
    }
    $array = array_values($array);
    foreach ($array as $key => $val) {
        foreach ($cols as $val2) {
            unset($val[$val2]);
        }
        $array[$key] = array_values($val);
    }
    return $array;
}

/**
 * Array to Tree helper
 *
 * This function tries to convert the array into a tree using the nodes,
 * specification
 *
 * @array => the matrix that you want to convert into a tree
 * @nodes => the dictionary used to the conversion, must to be an array with
 *           the fields used by each node, for example ["A,B,C","D,E,F"]
 * @nohead => set it to true to prevent the usage of the first row of the
 *            matrix as header, this option uses the letter as id of each
 *            element of the tree
 * @noletter => set it to true to prevent the usage of letters, if the
 *              previous option is set to true
 */
function __import_array2tree($array, $nodes, $nohead, $noletter)
{
    // Initial checks
    if (!is_array($array)) {
        return $array;
    }
    if (!count($array)) {
        return $array;
    }
    if (!__import_check_real_matrix($array)) {
        return $array;
    }
    // Continue
    if ($nohead) {
        $head = array();
        $num = 1;
        foreach ($array as $temp) {
            $num = max($num, count($temp));
        }
        for ($i = 0; $i < $num; $i++) {
            $head[] = $noletter ? $i : __import_col2name($i);
        }
    } else {
        $head = array_shift($array);
    }
    // Fix for duplicates and spaces
    $temp = array();
    foreach ($head as $temp2) {
        $temp2 = trim($temp2);
        set_array($temp, $temp2, "");
    }
    $head = array_keys($temp);
    // Continue
    if (!is_array($nodes) || !count($nodes)) {
        $nodes = array(range(0, count($head) - 1));
    } else {
        $col = 0;
        foreach ($nodes as $key => $val) {
            if (!is_array($val)) {
                if ($val == "") {
                    $val = array();
                } else {
                    $val = explode(",", $val);
                }
            }
            $nodes[$key] = array();
            foreach ($val as $key2 => $val2) {
                if (in_array($val2, $head)) {
                    $nodes[$key][$key2] = array_search($val2, $head);
                } elseif (__import_isname($val2)) {
                    $nodes[$key][$key2] = __import_name2col($val2);
                } elseif (!is_numeric($val2)) {
                    $nodes[$key][$key2] = $col;
                }
                $col++;
            }
        }
    }
    $result = array();
    foreach ($array as $line) {
        $parts = array();
        foreach ($nodes as $node) {
            $head2 = __import_array_intersect($head, $node);
            if (count($head2)) {
                $line2 = __import_array_intersect($line, $node);
                if (count($head2) > count($line2)) {
                    $temp = array();
                    foreach ($head2 as $key => $val) {
                        $temp[$key] = isset($line2[$key]) ? $line2[$key] : "";
                    }
                    $line2 = $temp;
                }
                if (count($head2) != count($line2)) {
                    return "Error: Internal error (" . __FUNCTION__ . ")";
                }
                $line3 = array_combine($head2, $line2);
                $hash = md5(serialize($line3));
                $parts[$hash] = $line3;
            }
        }
        __import_array2tree_set($result, $parts);
    }
    $result = __import_array2tree_clean($result);
    return $result;
}

/**
 * Array Intersect
 *
 * This function returns the same result that array_intersect_key($data,array_flip($filter))
 * maintaining the order of the filter array.
 *
 * @data => the array that you want to apply the filter
 * @filter => the array where obtain the keys to apply the filter
 */
function __import_array_intersect($data, $filter)
{
    $result = array();
    foreach ($filter as $field) {
        if (isset($data[$field])) {
            $result[$field] = $data[$field];
        }
    }
    return $result;
}

/**
 * Array to Tree Set helper
 *
 * This function tries to set values in a tree structure, to do it, it uses
 * the parts array that contains a list of paired keys and values used to move
 * by the tree setting the values of each pair of key val
 *
 * @result => the array where do you want to put the parts
 * @parts => an array with pairs of key val
 */
function __import_array2tree_set(&$result, $parts)
{
    $key = key($parts);
    $val = current($parts);
    unset($parts[$key]);
    if (count($parts)) {
        if (!isset($result[$key])) {
            $result[$key] = array("row" => $val,"rows" => array());
        }
        __import_array2tree_set($result[$key]["rows"], $parts);
    } else {
        set_array($result, $key, $val);
    }
}

/**
 * Array to Tree Clean helper
 *
 * This function tries to clean the tree by setting an automatic indexes
 *
 * @array => the array to clean
 */
function __import_array2tree_clean($array)
{
    $result = array();
    foreach ($array as $node) {
        if (isset($node["row"]) && isset($node["rows"])) {
            $result[] = array("row" => $node["row"],"rows" => __import_array2tree_clean($node["rows"]));
        } else {
            $result[] = $node;
        }
    }
    return $result;
}

/**
 * Tree to Array
 *
 * This function convert a tree into a 2D matrix, it's intended to convert
 * a tree structure into a csv, for example
 *
 * @array => the tree that you want to convert into a 2D matrix
 */
function __import_tree2array($array)
{
    $result = array();
    foreach ($array as $node) {
        if (isset($node["row"]) && isset($node["rows"])) {
            foreach (__import_tree2array($node["rows"]) as $row) {
                // Fix for duplicates
                $temp = $node["row"];
                foreach ($row as $key => $val) {
                    set_array($temp, $key, $val);
                }
                // Continue
                $result[] = $temp;
            }
        } else {
            $result[] = $node;
        }
    }
    return $result;
}

/**
 * Column to Name helper
 *
 * This function returns the name of the column from the position n
 *
 * @n => the position number
 *
 * Notes:
 *
 * This function was copied from:
 * - http://www.php.net/manual/en/function.base-convert.php#94874
 */
function __import_col2name($n)
{
    $r = '';
    for ($i = 1; $n >= 0 && $i < 10; $i++) {
        $r = chr(0x41 + (int)($n % pow(26, $i) / pow(26, $i - 1))) . $r;
        $n -= pow(26, $i);
    }
    return $r;
}

/**
 * Name to Column helper
 *
 * This function returns the position number of the column from the name
 *
 * @a => the column name
 *
 * Notes:
 *
 * This function was copied from:
 * - http://www.php.net/manual/en/function.base-convert.php#94874
 */
function __import_name2col($a)
{
    $r = 0;
    $l = strlen($a);
    for ($i = 0; $i < $l; $i++) {
        $r += pow(26, $i) * (ord($a[$l - $i - 1]) - 0x40);
    }
    return $r - 1;
}

/**
 * Is Name helper
 *
 * This function returns true if the name argument contains only valid letters
 * used in the name of the column
 *
 * @name => the name that you want to check
 */
function __import_isname($name)
{
    $len = strlen($name);
    for ($i = 0; $i < $len; $i++) {
        if ($name[$i] < 'A' || $name[$i] > 'Z') {
            return false;
        }
    }
    return true;
}

/**
 * Cell to Column and Row helper
 *
 * This function extract the column part and the row part from a cell name
 *
 * @cell => the cell that you want to process
 *
 * Notes:
 *
 * This function tries to retusn an array with two elements, for example, for
 * the cell AX23, the function returns [AX,23]
 */
function __import_cell2colrow($cell)
{
    $col = "";
    $row = "";
    $len = strlen($cell);
    for ($i = 0; $i < $len; $i++) {
        if ($cell[$i] >= 'A' && $cell[$i] <= 'Z') {
            $col .= $cell[$i];
        }
        if ($cell[$i] >= '0' && $cell[$i] <= '9') {
            $row .= $cell[$i];
        }
    }
    return array($col,$row);
}

/**
 * Get Keys
 *
 * This function tries to return an array with all the keys used internally
 * in the tree
 *
 * @array => the tree array that you want to process
 */
function __import_getkeys($array)
{
    $result = array();
    if (isset($array[0])) {
        $node = $array[0];
        if (isset($node["row"]) && isset($node["rows"])) {
            $result = array_merge(array_keys($node["row"]), __import_getkeys($node["rows"]));
        } else {
            $result = array_keys($node);
        }
    }
    return $result;
}

/**
 * Filter helper
 *
 * This function tries to apply a filter to a tree array, too allow to use
 * the evaluation system to allow to pass as filter an expression like this
 * A=M23
 *
 * @array => the tree array that you want to apply the filter
 * @filter => the filter to apply
 * @eval => set to 1 if you want to enable the eval feature
 */
function __import_filter($array, $filter, $eval = 0)
{
    $result = array();
    foreach ($array as $node) {
        if (__import_filter_rec($node, $filter, $eval)) {
            $result[] = $node;
        }
    }
    return $result;
}

/**
 * Filter Recursive helper
 *
 * This function is a helper of the previous function and is able to to the
 * same but with recursivity
 *
 * @node => the tree node that you want to filter
 * @filter => the filter to apply
 * @eval => set to 1 if you want to enable the eval feature
 * @parent => this parameter is intended to be used internaly by the function
 */
function __import_filter_rec($node, $filter, $eval, $parent = array())
{
    if (isset($node["row"]) && isset($node["rows"])) {
        // Normal filter
        foreach ($node["row"] as $val) {
            if (stripos($val, $filter) !== false) {
                return true;
            }
        }
        // Eval filter
        if ($eval) {
            $vars = array_merge($parent, array_values($node["row"]));
            $keys = array_keys($vars);
            foreach ($keys as $key => $val) {
                $keys[$key] = __import_col2name($val);
            }
            $vars = array_combine($keys, $vars);
            capture_next_error();
            $result = eval_protected($filter, $vars);
            $error = get_clear_error();
            if ($result && !$error) {
                return true;
            }
        }
        // Recursive call
        foreach ($node["rows"] as $node2) {
            if (__import_filter_rec($node2, $filter, $eval, array_merge($parent, array_values($node["row"])))) {
                return true;
            }
        }
    } else {
        // Normal filter
        foreach ($node as $val) {
            if (stripos($val, $filter) !== false) {
                return true;
            }
        }
        // Eval filter
        if ($eval) {
            $vars = array_merge($parent, array_values($node));
            $keys = array_keys($vars);
            foreach ($keys as $key => $val) {
                $keys[$key] = __import_col2name($val);
            }
            $vars = array_combine($keys, $vars);
            capture_next_error();
            $result = eval_protected($filter, $vars);
            $error = get_clear_error();
            if ($result && !$error) {
                return true;
            }
        }
    }
}

/**
 * Apply Patch
 *
 * This function is able to apply a patch in the tree array, this allow to
 * update the desired branch of the tree using a xpath notation
 *
 * @array => the array that you want to apply the patch
 * @key => the xpath where you want to apply the patch
 * @val => the val that you want to put in the desired xpath
 */
function __import_apply_patch(&$array, $key, $val)
{
    $key = explode("/", $key);
    $key = array_reverse($key);
    array_pop($key);
    __import_apply_patch_rec($array, $key, $val);
}

/**
 * Apply Patch Recursive helper
 *
 * This function is a helper of the previous function and is able to to the
 * same but with recursivity
 *
 * @array => the array that you want to apply the patch
 * @key => the xpath where you want to apply the patch
 * @val => the val that you want to put in the desired xpath
 */
function __import_apply_patch_rec(&$array, $key, $val)
{
    $key0 = array_pop($key);
    $key1 = array_pop($key);
    if ($key0 == "row") {
        if (isset($array["rows"][$key1])) {
            __import_apply_patch_rec($array["rows"][$key1], $key, $val);
        } elseif (isset($array[$key1])) {
            __import_apply_patch_rec($array[$key1], $key, $val);
        } else {
            show_php_error(array("phperror" => "Path '{$key0}' for '{$key1}' not found"));
        }
    } elseif ($key0 == "col") {
        if (isset($array["row"]) && isset($array["rows"])) {
            $col = 0;
            foreach ($array["row"] as $key2 => $val2) {
                if ($col == $key1) {
                    $array["row"][$key2] = $val;
                }
                $col++;
            }
        } else {
            $col = 0;
            foreach ($array as $key2 => $val2) {
                if ($col == $key1) {
                    $array[$key2] = $val;
                }
                $col++;
            }
        }
    } else {
        show_php_error(array("phperror" => "Unknown '{$key0}' for '{$key1}'"));
    }
}

/**
 * Make Table ASCII
 *
 * This table is intended for debug purposes and is able to paint in ascii
 * mode the contents of a matrix
 *
 * @rows => the contents of the matrix to paint
 * @head => set to true if you want to use the first row as header
 * @compact => set to true if you want to minify the ascii table
 */
function __import_make_table_ascii($array)
{
    // Preparar datos
    if (!is_array($array["rows"])) {
        $array["rows"] = array(array($array["rows"]));
        $array["head"] = 0;
    }
    if (!count($array["rows"])) {
        $array["rows"] = array(array("Data not found"));
        $array["head"] = 0;
    }
    // Inicializar variables locales
    $rows = isset($array["rows"]) ? $array["rows"] : array();
    $head = isset($array["head"]) ? $array["head"] : 1;
    $compact = isset($array["compact"]) ? $array["compact"] : 0;
    // Calcular alineaciones
    $aligns = array();
    foreach ($rows as $row) {
        foreach ($row as $key => $val) {
            if (!isset($aligns[$key])) {
                $aligns[$key] = array("L" => 0,"R" => 0);
            }
            if (is_numeric($val)) {
                $aligns[$key]["R"]++;
            } elseif (substr($val, -1, 1) == "%") {
                $aligns[$key]["R"]++;
            } elseif (substr($val, -1, 1) == "€") {
                $aligns[$key]["R"]++;
            } else {
                $aligns[$key]["L"]++;
            }
        }
    }
    foreach ($aligns as $key => $val) {
        $aligns[$key] = ($val["R"] > $val["L"]) ? "R" : "L";
    }
    // Calcular medidas
    $widths = array();
    if ($head) {
        array_unshift($rows, array_combine(array_keys($rows[0]), array_keys($rows[0])));
    }
    foreach ($rows as $row) {
        foreach ($row as $key => $val) {
            if (!isset($widths[$key])) {
                $widths[$key] = 0;
            }
            $widths[$key] = max(mb_strlen($val), $widths[$key]);
        }
    }
    // Pintar tabla
    ob_start();
    foreach ($widths as $width) {
        echo "+" . str_repeat("-", $width + ($compact ? 0 : 2));
    }
    echo "+\n";
    foreach ($rows as $index => $row) {
        if ($index == 1 && $head) {
            foreach ($widths as $width) {
                echo "+" . str_repeat("-", $width + ($compact ? 0 : 2));
            }
            echo "+\n";
        }
        foreach ($row as $key => $val) {
            echo "|";
            if ($aligns[$key] == "R") {
                echo str_repeat(" ", $widths[$key] - mb_strlen($val));
            }
            echo ($compact ? "" : " ") . $val . ($compact ? "" : " ");
            if ($aligns[$key] == "L") {
                echo str_repeat(" ", $widths[$key] - mb_strlen($val));
            }
        }
        echo "|\n";
    }
    foreach ($widths as $width) {
        echo "+" . str_repeat("-", $width + ($compact ? 0 : 2));
    }
    echo "+";
    $buffer = ob_get_clean();
    // Bye bye
    return $buffer;
}
