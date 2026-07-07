<?php

/**
 *  ____        _ _    ___  ____  _  _
 * / ___|  __ _| | |_ / _ \/ ___|| || |
 * \___ \ / _` | | __| | | \___ \| || |_
 *  ___) | (_| | | |_| |_| |___) |__   _|
 * |____/ \__,_|_|\__|\___/|____/   |_|
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
 */

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration
// phpcs:disable PSR1.Methods.CamelCapsMethodName

/**
 * PDF helper module
 *
 * This file contains useful functions related to PDF generation using TCPDF library
 * including custom PDF class extensions and various PDF manipulation utilities
 */

define('K_TCPDF_EXTERNAL_CONFIG', true);// to prevent the load of tcpdf_config.php
define('K_TCPDF_THROW_EXCEPTION_ERROR', true); // to force a throw exception on error
define('K_PATH_IMAGES', ''); // to prevent open_basedir restriction
define('K_PATH_FONTS', realpath('lib/tc-lib-pdf/vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));
define('K_ALLOWED_PATHS', [dirname(dirname(getcwd()))]); // to allow the utest execution
require_once 'lib/tc-lib-pdf/vendor/autoload.php';
require_once 'lib/tcpdf/vendor/autoload.php';

/**
 * Evaluate dynamic value in PDF context
 *
 * @input => Expression to evaluate
 * @row   => Data row for variable substitution
 * @pdf   => PDF object reference
 *
 * Returns the evaluated result
 */
function __pdf_eval_value($input, $row, $pdf)
{
    return eval("return $input;");
}

/**
 * Evaluate array values in PDF context
 *
 * @array => Input array with expressions
 * @row   => Data row for variable substitution
 * @pdf   => PDF object reference
 *
 * Returns array with evaluated values
 */
function __pdf_eval_array($array, $row, $pdf)
{
    foreach ($array as $key => $val) {
        $array[$key] = __pdf_eval_value($val, $row, $pdf);
    }
    return $array;
}

/**
 * Advanced string explosion with quote and parentheses awareness
 *
 * @separator => Delimiter character
 * @str       => Input string to explode
 * @limit     => Maximum number of splits
 *
 * Returns array of exploded parts
 */
function __pdf_eval_explode($separator, $str, $limit = 0)
{
    $result = [];
    $len = strlen($str);
    $ini = 0;
    $count = 0;
    $single = 0;
    $double = 0;
    $parentheses = 0;
    for ($i = 0; $i < $len; $i++) {
        $letter = $str[$i];
        if ($letter === "'") {
            $single = ($single + 1) % 2;
        } elseif ($letter === '"') {
            $double = ($double + 1) % 2;
        } elseif ($letter === '(') {
            $parentheses++;
        } elseif ($letter === ')') {
            $parentheses--;
        }
        if ($letter === $separator && $single === 0 && $double === 0 && $parentheses === 0) {
            if ($limit > 0 && $count === $limit - 1) {
                $result[] = substr($str, $ini);
                $ini = $i;
                break;
            } else {
                $result[] = substr($str, $ini, $i - $ini);
                $ini = $i + 1;
                $count++;
            }
        }
    }
    if ($i !== $ini) {
        $result[] = substr($str, $ini, $i - $ini);
    }
    return $result;
}

/**
 * TODO
 *
 * TODO
 */
function __pdf_check_y($pdf, $offset = 0)
{
    if (floatval($pdf->y + $offset) > floatval($pdf->h - $pdf->bMargin)) {
        $oldx = $pdf->GetX();
        $pdf->AddPage();
        $pdf->SetXY($oldx, $pdf->tMargin);
        return true;
    }
    return false;
}

/**
 * Process PDF template tags and generate PDF content
 *
 * @array => PDF template definition array
 * @row   => Data row for template evaluation
 *
 * Returns the generated PDF object or output array
 */
function __pdf_eval_pdftag($array, $row = [])
{
    require_once 'php/lib/color.php';
    static $pdf = null;

    static $arr_header = [];
    static $row_header = [];
    static $arr_footer = [];
    static $row_footer = [];
    static $list_pages = [];

    // Support for ltr and rtl langs
    $dir = 'ltr';
    if (isset($row['dir'])) {
        $dir = $row['dir'];
    }
    $rtl = ['ltr' => ['L' => 'L', 'C' => 'C', 'R' => 'R'], 'rtl' => ['L' => 'R', 'C' => 'C', 'R' => 'L']];

    $fonts = ['normal' => 'atkinsonhyperlegiblenext', 'mono' => 'atkinsonhyperlegiblemono'];

    if (!is_array($array)) {
        show_php_error(['phperror' => 'Array not found']);
    }
    foreach ($array as $key => $val) {
        $key = fix_key($key);
        static $bool = true;
        switch ($key) {
            case 'eval':
                $bool = __pdf_eval_value($val, $row, $pdf);
                break;
            case 'constructor':
                // format => orientation, unit, format
                if (!$bool) {
                    break;
                }
                $temp = __pdf_eval_array(__pdf_eval_explode(',', $val), $row, $pdf);
                $pdf = new TCPDF($temp[0], $temp[1], $temp[2]);
                $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(false);
                $pdf->SetCreator(get_name_version_revision());
                $pdf->SetDisplayMode('fullwidth', 'continuous');
                $pdf->setRTL($dir === 'rtl');
                break;
            case 'margins':
                // format => top, right, bottom, left
                if (!$bool) {
                    break;
                }
                $temp = __pdf_eval_array(__pdf_eval_explode(',', $val), $row, $pdf);
                $pdf->SetMargins($temp[3], $temp[0], $temp[1]);
                $pdf->SetAutoPageBreak(true, $temp[2]);
                break;
            case 'foreach':
                // requires query node with a valid sql sentence
                if (!$bool) {
                    break;
                }
                if (!isset($val['query'])) {
                    show_php_error(['phperror' => 'Foreach without query!!!']);
                }
                $query = __pdf_eval_value($val['query'], $row, $pdf);
                unset($val['query']);
                $result = db_query($query);
                while ($row2 = db_fetch_row($result)) {
                    __pdf_eval_pdftag($val, $row2);
                }
                db_free($result);
                break;
            case 'output':
                // format => filename and defines the name of the output
                if (!$bool) {
                    break;
                }
                $num_pages = $pdf->getNumPages();
                for ($page = 1; $page <= $num_pages; $page++) {
                    $pdf->setPage($page);
                    __pdf_eval_pdftag($arr_footer, $row_footer);
                }
                $name = __pdf_eval_value($val, $row, $pdf);
                $buffer = $pdf->Output($name, 'S');
                return [
                    'name' => $name,
                    'data' => $buffer,
                ];
            case 'header':
                // format => node
                if (!$bool) {
                    break;
                }
                $arr_header = $val;
                $row_header = $row;
                break;
            case 'footer':
                // format => node
                if (!$bool) {
                    break;
                }
                $arr_footer = $val;
                $row_footer = $row;
                break;
            case 'newpage':
                // format => [orientation]
                if (!$bool) {
                    break;
                }
                if ($val) {
                    $pdf->AddPage(__pdf_eval_value($val, $row, $pdf));
                } else {
                    $pdf->AddPage();
                }
                __pdf_eval_pdftag($arr_header, $row_header);
                break;
            case 'font':
                // format => family, style, size, color
                if (!$bool) {
                    break;
                }
                $temp = __pdf_eval_array(__pdf_eval_explode(',', $val, 4), $row, $pdf);
                $temp = [$temp[0], $temp[1], $temp[2],
                    color2dec($temp[3], 'R'), color2dec($temp[3], 'G'), color2dec($temp[3], 'B'),
                ];
                if (isset($fonts[$temp[0]])) {
                    $temp[0] = $fonts[$temp[0]];
                }
                $pdf->SetFont($temp[0], $temp[1], $temp[2]);
                $pdf->SetTextColor($temp[3], $temp[4], $temp[5]);
                break;
            case 'image':
                // format => left, top, width, height, file, [angle]
                if (!$bool) {
                    break;
                }
                $temp = __pdf_eval_array(__pdf_eval_explode(',', $val, 6), $row, $pdf);
                if (isset($temp[5])) {
                    $pdf->StartTransform();
                    $pdf->Rotate(floatval($temp[5]), $temp[0], $temp[1]);
                }
                if (!file_exists($temp[4])) {
                    show_php_error(['phperror' => "File {$temp[4]} not found"]);
                }
                $pdf->Image($temp[4], $temp[0], $temp[1], $temp[2], $temp[3]);
                if (isset($temp[5])) {
                    $pdf->StopTransform();
                }
                break;
            case 'text':
                // format => left, top, text, [angle]
                if (!$bool) {
                    break;
                }
                $temp = __pdf_eval_array(__pdf_eval_explode(',', $val, 4), $row, $pdf);
                if (isset($temp[3])) {
                    $pdf->StartTransform();
                    $pdf->Rotate(floatval($temp[3]), $temp[0], $temp[1]);
                }
                $pdf->SetXY($temp[0], $temp[1]);
                $pdf->Cell(0, 0, strval($temp[2]));
                if (isset($temp[3])) {
                    $pdf->StopTransform();
                }
                break;
            case 'textarea':
                // format => left, top, width, height, align(L,C,R), text, [angle]
                if (!$bool) {
                    break;
                }
                $temp = __pdf_eval_array(__pdf_eval_explode(',', $val, 7), $row, $pdf);
                if (isset($temp[6])) {
                    $pdf->StartTransform();
                    $pdf->Rotate(floatval($temp[6]), $temp[0], $temp[1]);
                }
                $pdf->SetXY($temp[0], $temp[1]);
                if (!isset($temp[6])) {
                    if (__pdf_check_y($pdf, $temp[3])) {
                        __pdf_eval_pdftag($arr_header, $row_header);
                        $pdf->SetXY($temp[0], $temp[1]);
                    }
                }
                $pdf->MultiCell($temp[2], $temp[3], strval($temp[5]), 0, $rtl[$dir][$temp[4]]);
                if (isset($temp[6])) {
                    $pdf->StopTransform();
                }
                break;
            case 'color':
                // format => draw color, fill color
                if (!$bool) {
                    break;
                }
                $temp = __pdf_eval_array(__pdf_eval_explode(',', $val, 2), $row, $pdf);
                $temp = [color2dec($temp[0], 'R'), color2dec($temp[0], 'G'), color2dec($temp[0], 'B'),
                    color2dec($temp[1], 'R'), color2dec($temp[1], 'G'), color2dec($temp[1], 'B'),
                ];
                $pdf->SetDrawColor($temp[0], $temp[1], $temp[2]);
                $pdf->SetFillColor($temp[3], $temp[4], $temp[5]);
                break;
            case 'rect':
                // format => left, top, width, height, style (D,F,DF), [line width], [radious]
                if (!$bool) {
                    break;
                }
                $temp = __pdf_eval_array(__pdf_eval_explode(',', $val, 7), $row, $pdf);
                if (isset($temp[5])) {
                    $pdf->SetLineWidth($temp[5]);
                }
                if (isset($temp[6])) {
                    $pdf->RoundedRect($temp[0], $temp[1], $temp[2], $temp[3], $temp[6], '1111', $temp[4]);
                } else {
                    $pdf->Rect($temp[0], $temp[1], $temp[2], $temp[3], $temp[4]);
                }
                break;
            case 'line':
                // format => left, top, width ,height, [line width]
                if (!$bool) {
                    break;
                }
                $temp = __pdf_eval_array(__pdf_eval_explode(',', $val, 5), $row, $pdf);
                if (isset($temp[4])) {
                    $pdf->SetLineWidth($temp[4]);
                }
                $pdf->Line($temp[0], $temp[1], $temp[0] + $temp[2], $temp[1] + $temp[3]);
                break;
            case 'setxy':
                // format => left, top
                if (!$bool) {
                    break;
                }
                $temp = __pdf_eval_array(__pdf_eval_explode(',', $val, 2), $row, $pdf);
                $pdf->SetXY($temp[0], $temp[1]);
                if (__pdf_check_y($pdf)) {
                    __pdf_eval_pdftag($arr_header, $row_header);
                }
                break;
            case 'getxy':
                // format => index for x, index for y
                if (!$bool) {
                    break;
                }
                $temp = __pdf_eval_array(__pdf_eval_explode(',', $val, 2), $row, $pdf);
                $row[$temp[0]] = $pdf->GetX();
                $row[$temp[1]] = $pdf->GetY();
                break;
            case 'pageno':
                // format => left, top, [text]
                // format => left, top, width, height, align(L,C,R), [text]
                if (!$bool) {
                    break;
                }
                $temp = __pdf_eval_array(__pdf_eval_explode(',', $val, 6), $row, $pdf);
                if (!isset($temp[4])) {
                    $pdf->SetXY($temp[0], $temp[1]);
                    if (!isset($temp[2])) {
                        $temp[2] = '%s/%s';
                    }
                    $temp[2] = sprintf($temp[2], $pdf->PageNo(), $pdf->getNumPages());
                    $pdf->Cell(0, 0, $temp[2]);
                } else {
                    $pdf->SetXY($temp[0], $temp[1]);
                    if (!isset($temp[5])) {
                        $temp[5] = '%s/%s';
                    }
                    $temp[5] = sprintf($temp[5], $pdf->PageNo(), $pdf->getNumPages());
                    if (__pdf_check_y($pdf, $temp[3])) {
                        __pdf_eval_pdftag($arr_header, $row_header);
                        $pdf->SetXY($temp[0], $temp[1]);
                    }
                    $pdf->MultiCell($temp[2], $temp[3], strval($temp[5]), 0, $rtl[$dir][$temp[4]]);
                }
                break;
            case 'checky':
                // format => height to check
                if (!$bool) {
                    break;
                }
                $temp = __pdf_eval_array(__pdf_eval_explode(',', $val, 1), $row, $pdf);
                if (__pdf_check_y($pdf, $temp[0])) {
                    __pdf_eval_pdftag($arr_header, $row_header);
                }
                break;
            case 'link':
                // format => left, top, text, url
                if (!$bool) {
                    break;
                }
                $temp = __pdf_eval_array(__pdf_eval_explode(',', $val, 4), $row, $pdf);
                $pdf->SetXY($temp[0], $temp[1]);
                $pdf->Cell(0, 0, $temp[2], 0, 0, '', false, $temp[3]);
                break;
            default:
                show_php_error(['phperror' => "Eval PDF Tag error: $key"]);
        }
    }
    return $pdf;
}

/**
 * Generate PDF from template file
 *
 * @file => Path to PDF template file
 * @row  => Data row for template evaluation
 *
 * Returns array containing PDF name, type and base64 encoded data
 */
function pdf($file, $row = [])
{
    static $cache = [];
    $hash = md5(serialize([$file, $row]));
    if (isset($cache[$hash])) {
        return $cache[$hash];
    }
    $xml = xmlfile2array($file);
    $pdf = __pdf_eval_pdftag($xml, $row);
    if ($pdf instanceof TCPDF) {
        show_php_error(['phperror' => 'Output node not found in template']);
    }
    $cache[$hash] = [
        'name' => $pdf['name'],
        'type' => 'application/pdf',
        'data' => base64_encode($pdf['data']),
    ];
    return $cache[$hash];
}

/**
 * Convert various file types to PDF
 *
 * @input => Path to input file to convert
 *
 * Returns PDF content as string
 */
function __pdf_all2pdf($input)
{
    $type = saltos_content_type($input);
    $type0 = saltos_content_type0($type);
    $type1 = saltos_content_type1($type);

    // For plain and html text
    if (in_array($type0, ['text', 'message'], true)) {
        $pdf = new TCPDF('P', 'mm', 'A4');
        $pdf->SetCreator(get_name_version_revision());
        $pdf->SetDisplayMode('fullwidth', 'continuous');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();
        $pdf->SetFont('atkinsonhyperlegiblenext', '', 10);
        if ($type1 === 'html') {
            $pdf->WriteHTML(file_get_contents($input));
        } else {
            $pdf->Write(0, file_get_contents($input));
        }
        $buffer = $pdf->Output('output.pdf', 'S');
        return $buffer;
    }

    // For images
    if ($type0 === 'image') {
        list($width, $height) = getimagesize($input);
        if (in_array($type1, ['jpeg', 'tiff'], true)) {
            $exif = exif_read_data($input);
            $orientation = $exif['Orientation'] ?? 1;
            if (in_array($orientation, [6, 8], true)) {
                list($width, $height) = [$height, $width];
            }
        }
        $pdf = new TCPDF('', 'mm', [$width, $height]);
        $pdf->SetCreator(get_name_version_revision());
        $pdf->SetDisplayMode('fullwidth', 'continuous');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->AddPage();
        $pdf->Image($input, 0, 0, $width, $height);
        $buffer = $pdf->Output('output.pdf', 'S');
        return $buffer;
    }

    // Unsupported type
    $pdf = new TCPDF('L', 'mm', 'A4');
    $pdf->SetCreator(get_name_version_revision());
    $pdf->SetDisplayMode('fullwidth', 'continuous');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $buffer = $pdf->Output('output.pdf', 'S');
    return $buffer;
}
