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
 * Export helper module
 *
 * This fie contains useful functions related to export data, allow to generate outputs in formats
 * suck as excel, csv, edi, json and xml
 */

/**
 * Export File main function
 *
 * This function is intended to export data in the supported formats
 *
 * @type     => can be xml, csv, xls, xlsx, edi or json
 * @data     => the matrix to export
 * @sep      => separator char used only by csv format
 * @eol      => enf of line char used by csv and xml format
 * @encoding => charset used by csv and xml format
 * @replace  => array with two elements, from and to, used to do replacements of the matrix values
 * @escape   => array with two elements, char and mode, used to specify the escape character and the
 *              escape mode
 * @title    => title used only by excel format
 * @file     => local filename used to store the results
 * @ext      => extension used for the filename if provided
 * @wrap     => boolean argument used only for edi indentation
 * @indent   => boolean argument used only for json indentation
 * @prefn    => function executed between the load and the tree construction
 * @notree   => boolean to enable or disable the tree2array feature
 * @postfn   => function executed after the tree construction
 * @novoid   => boolean to enable or disable the removevoid feature
 *
 * If file argument is specified, void string is returned
 * If file argument is not specified, then they will returns all data
 */
function export_file($args)
{
    //~ echo "<pre>".sprintr($args)."</pre>";die();
    // Check parameters
    if (!isset($args["type"])) {
        show_php_error(["phperror" => "Unknown type"]);
    }
    if (!isset($args["data"])) {
        show_php_error(["phperror" => "Unknown data"]);
    }
    if (!isset($args["sep"])) {
        $args["sep"] = ";";
    }
    if (!isset($args["eol"])) {
        $args["eol"] = "\n";
    }
    if (!isset($args["encoding"])) {
        $args["encoding"] = "UTF-8";
    }
    if (!isset($args["replace"])) {
        $args["replace"] = ["from" => "", "to" => ""];
    }
    if (!isset($args["escape"])) {
        $args["escape"] = ["char" => '"', "mode" => "auto"];
    }
    if (!isset($args["title"])) {
        $args["title"] = "";
    }
    if (!isset($args["file"])) {
        $args["file"] = "";
    }
    if (!isset($args["ext"])) {
        $args["ext"] = "";
    }
    if (!isset($args["wrap"])) {
        $args["wrap"] = false;
    }
    if (!isset($args["indent"])) {
        $args["indent"] = false;
    }
    if (!isset($args["prefn"])) {
        $args["prefn"] = "";
    }
    if (!isset($args["notree"])) {
        $args["notree"] = true;
        if ($args["type"] != "xml") {
            $args["notree"] = false;
        }
    }
    if (!isset($args["postfn"])) {
        $args["postfn"] = "";
    }
    if (!isset($args["novoid"])) {
        $args["novoid"] = false;
    }
    // New part that apply the same concept used in the import_file
    if ($args["prefn"]) {
        $args["data"] = $args["prefn"]($args["data"], $args);
        if (!is_array($args["data"])) {
            return $args["data"];
        }
    }
    if (!$args["notree"]) {
        $args["data"] = __export_tree2array($args["data"]);
        //~ if (!is_array($args["data"])) {
            //~ return $args["data"];
        //~ }
    }
    if ($args["postfn"]) {
        $args["data"] = $args["postfn"]($args["data"], $args);
        if (!is_array($args["data"])) {
            return $args["data"];
        }
    }
    if (!$args["novoid"]) {
        $args["data"] = __import_removevoid($args["data"]);
        //~ if (!is_array($args["data"])) {
            //~ return $args["data"];
        //~ }
    }
    // Continue
    switch ($args["type"]) {
        case "xml":
            $buffer = __export_file_xml($args["data"], $args["eol"], $args["encoding"]);
            break;
        case "csv":
            $buffer = __export_file_csv(
                $args["data"], $args["sep"], $args["eol"],
                $args["encoding"], $args["replace"], $args["escape"]
            );
            break;
        case "xls":
            $buffer = __export_file_excel($args["data"], $args["title"], "Xls");
            break;
        case "xlsx":
            $buffer = __export_file_excel($args["data"], $args["title"], "Xlsx");
            break;
        case "ods":
            $buffer = __export_file_excel($args["data"], $args["title"], "Ods");
            break;
        case "edi":
            $buffer = __export_file_edi($args["data"], $args["wrap"]);
            break;
        case "json":
            $buffer = __export_file_json($args["data"], $args["indent"]);
            break;
        default:
            show_php_error(["phperror" => "Unknown type '{$args["type"]}' for file '{$args["file"]}'"]);
    }
    if ($args["file"] != "") {
        if ($args["ext"] == "") {
            $args["ext"] = $args["type"];
        }
        if (strtolower(extension($args["file"])) != $args["ext"]) {
            $args["file"] .= "." . $args["ext"];
        }
        file_put_contents($args["file"], $buffer);
        return "";
    }
    return $buffer;
}

/**
 * Export File XML
 *
 * This function is intended to export data in xml format
 *
 * @matrix   => the matrix to export
 * @eol      => enf of line char
 * @encoding => charset used
 *
 * They will returns all data
 */
function __export_file_xml($matrix, $eol = "\n", $encoding = "UTF-8")
{
    $buffer = str_replace("UTF-8", $encoding, "<?xml version='1.0' encoding='UTF-8' ?>\n");
    $buffer .= __array2xml_write_nodes($matrix, 0);
    $buffer = str_replace("\n", $eol, $buffer);
    $buffer = mb_convert_encoding($buffer, $encoding, "UTF-8");
    return $buffer;
}

/**
 * Export File CSV
 *
 * This function is intended to export data in csv format
 *
 * @matrix   => the matrix to export
 * @sep      => separator char
 * @eol      => enf of line char
 * @encoding => charset used
 * @replace  => array with two elements, from and to, used to do replacements of the matrix values
 * @escape   => array with two elements, char and mode, used to specify the escape character and the
 *              escape mode
 *
 * They will returns all data
 */
function __export_file_csv(
    $matrix,
    $sep = ";",
    $eol = "\r\n",
    $encoding = "UTF-8",
    $replace = ["from" => "", "to" => ""],
    $escape = ["char" => '"', "mode" => "auto"]
) {
    $sep = __import_specialchars($sep);
    $eol = __import_specialchars($eol);
    $replace["from"] = __import_specialchars(explode(",", $replace["from"]));
    $replace["to"] = __import_specialchars($replace["to"]);
    $xchar = $escape["char"];
    $buffer = [];
    foreach ($matrix as $key => $val) {
        $val = str_replace($replace["from"], $replace["to"], $val);
        foreach ($val as $key2 => $val2) {
            $val2 = trim($val2);
            if ($escape["mode"] == "auto") {
                $has_sep = strpos($val2, $sep) !== false ? 1 : 0;
                $has_new = strpos($val2, "\n") !== false ? 1 : 0;
                $has_ret = strpos($val2, "\r") !== false ? 1 : 0;
                $has_tab = strpos($val2, "\t") !== false ? 1 : 0;
                if ($has_sep + $has_new + $has_ret + $has_tab) {
                    $val2 = $xchar . str_replace($xchar, $xchar . $xchar, $val2) . $xchar;
                }
            } elseif (eval_bool($escape["mode"])) {
                $val2 = $xchar . str_replace($xchar, $xchar . $xchar, $val2) . $xchar;
            }
            $val[$key2] = $val2;
        }
        $buffer[] = implode($sep, $val);
    }
    $buffer = implode($eol, $buffer);
    $buffer = mb_convert_encoding($buffer, $encoding, "UTF-8");
    return $buffer;
}

/**
 * Export File Excel
 *
 * This function is intended to export data in excel format
 *
 * @matrix => the matrix to export
 * @title  => title used in the excel file
 * @type   => can be Xls or Xlsx
 *
 * They will returns all data
 */
function __export_file_excel($matrix, $title = "", $type = "Xlsx")
{
    require_once "lib/phpspreadsheet/vendor/autoload.php";
    $objPHPExcel = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $objPHPExcel->getProperties()->setCreator(get_name_version_revision());
    $objPHPExcel->getProperties()->setLastModifiedBy(current_datetime());
    if ($title != "") {
        $objPHPExcel->getProperties()->setTitle($title);
        $objPHPExcel->getProperties()->setSubject($title);
        $objPHPExcel->getProperties()->setDescription($title);
        $objPHPExcel->getProperties()->setKeywords($title);
        $objPHPExcel->getProperties()->setCategory($title);
    }
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->fromArray($matrix, null, "A1");
    for ($i = 0; $i < count($matrix[0]); $i++) {
        $objPHPExcel->getActiveSheet()->getColumnDimension(__import_col2name($i))->setAutoSize(true);
    }
    if ($title != "") {
        $objPHPExcel->getActiveSheet()->setTitle(substr($title, 0, 31));
    }
    // Convert all long numbers to string
    $row = 0;
    foreach ($matrix as $key => $val) {
        $col = 0;
        foreach ($val as $key2 => $val2) {
            if (is_numeric($val2)) {
                if (strlen($val2) > 15) {
                    if (substr($val2, 0, 1) != "0") {
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit(
                            __import_col2name($col) . strval($row + 1),
                            $val2,
                            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                        );
                    }
                }
            }
            $col++;
        }
        $row++;
    }
    // Continue
    $objWriter = PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, $type);
    ob_start();
    $objWriter->save("php://output");
    $buffer = ob_get_clean();
    return $buffer;
}

/*
 * Export File EDI
 *
 * This function is intended to export data in edi format
 *
 * @matrix => the matrix to export
 * @wrap => boolean argument to enable or disable the wrap feature
 * @encoding => charset used
 *
 * They will returns all data
 */
function __export_file_edi($matrix, $wrap = false)
{
    // Convert all items to string
    foreach ($matrix as $key => $line) {
        foreach ($line as $key2 => $field) {
            if (is_array($field)) {
                foreach ($field as $key3 => $subfield) {
                    if (is_array($subfield)) {
                        show_php_error(["phperror" => "Arrays in subfields not allowed"]);
                    } else {
                        $matrix[$key][$key2][$key3] = strval($subfield);
                    }
                }
            } else {
                $matrix[$key][$key2] = strval($field);
            }
        }
    }
    // Continue
    require_once "lib/edifact/vendor/autoload.php";
    $encoder = new EDI\Encoder();
    $encoder->encode($matrix, $wrap);
    $buffer = $encoder->get();
    return $buffer;
}

/**
 * Export File JSON
 *
 * This function is intended to export data in json format
 *
 * @matrix => the matrix to export
 * @indent => boolean argument to enable or disable the indent feature
 *
 * They will returns all data
 */
function __export_file_json($matrix, $indent = false)
{
    $flags = 0;
    if ($indent) {
        $flags |=  JSON_PRETTY_PRINT;
    }
    $buffer = json_encode($matrix, $flags);
    return $buffer;
}

/**
 * Tree to Array
 *
 * This function convert a tree into a 2D matrix, it's intended to convert
 * a tree structure into a csv, for example
 *
 * @array => the tree that you want to convert into a 2D matrix
 */
function __export_tree2array($array)
{
    $result = [];
    foreach ($array as $node) {
        if (isset($node["row"]) && isset($node["rows"])) {
            foreach (__export_tree2array($node["rows"]) as $row) {
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
 * Get Keys
 *
 * This function tries to return an array with all the keys used internally
 * in the tree
 *
 * @array => the tree array that you want to process
 */
function __export_getkeys($array)
{
    $result = [];
    if (isset($array[0])) {
        $node = $array[0];
        if (isset($node["row"]) && isset($node["rows"])) {
            $result = array_merge(array_keys($node["row"]), __export_getkeys($node["rows"]));
        } else {
            $result = array_keys($node);
        }
    }
    return $result;
}
