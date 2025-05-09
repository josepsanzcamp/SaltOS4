<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2025 by Josep Sanz Campderrós
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
 * Unoconv library
 *
 * This file contains all functions that allow conversions between formats like
 * docx, xlsx and more to pdf, too includes the ocr code that allow to get text
 * from images.
 */

/**
 * Unoconv to PDF
 *
 * This function allow to convert all input files into their equivalent pdf file
 *
 * @input => the file that you want to process
 */
function unoconv2pdf($input)
{
    $output = get_cache_file($input, '.pdf');
    if (!file_exists($output)) {
        $type = saltos_content_type($input);
        $ext = strtolower(extension($input));
        $type0 = saltos_content_type0($type);
        if ($type == 'application/pdf') {
            copy($input, $output);
        } elseif (
            (in_array($ext, __unoconv_list()) && !in_array($type0, ['audio', 'video'])) ||
            in_array($type0, ['text', 'message', 'image'])
        ) {
            __unoconv_all2pdf($input, $output);
        }
        if (!file_exists($output)) {
            file_put_contents($output, '');
        }
        chmod_protected($output, 0666);
    }
    return file_get_contents($output);
}

/**
 * Unoconv to TXT
 *
 * This function allow to convert all input files into their equivalent txt file
 *
 * @input => the file that you want to process
 */
function unoconv2txt($input)
{
    $output = get_cache_file($input, '.txt');
    if (!file_exists($output)) {
        $type = saltos_content_type($input);
        $ext = strtolower(extension($input));
        $type0 = saltos_content_type0($type);
        if (in_array($type, ['text/plain', 'application/json'])) {
            copy($input, $output);
        } elseif ($type == 'text/html') {
            file_put_contents($output, html2text(file_get_contents($input)));
        } elseif ($type == 'application/pdf') {
            __unoconv_pdf2txt($input, $output);
            if (!file_exists($output) || trim(file_get_contents($output)) == '') {
                file_put_contents($output, __unoconv_pdf2ocr($input));
            }
        } elseif (
            (in_array($ext, __unoconv_list()) && !in_array($type0, ['image', 'audio', 'video'])) ||
            in_array($type0, ['text', 'message'])
        ) {
            $pdf = get_cache_file($input, '.pdf');
            if (!file_exists($pdf)) {
                __unoconv_all2pdf($input, $pdf);
            }
            if (file_exists($pdf)) {
                chmod_protected($pdf, 0666);
                __unoconv_pdf2txt($pdf, $output);
                if (!file_exists($output) || trim(file_get_contents($output)) == '') {
                    file_put_contents($output, __unoconv_pdf2ocr($pdf));
                }
            }
        } elseif ($type0 == 'image') {
            file_put_contents($output, __unoconv_img2ocr($input));
        }
        if (!file_exists($output)) {
            file_put_contents($output, '');
        } else {
            file_put_contents($output, getutf8(file_get_contents($output)));
        }
        chmod_protected($output, 0666);
    }
    return file_get_contents($output);
}

/**
 * Unoconv list
 *
 * This function returns an array with all suported extensions by libreoffice
 */
function __unoconv_list()
{
    if (!check_commands('soffice')) {
        return [];
    }
    return [
        'bib', 'doc', 'xml', 'docx', 'fodt', 'html', 'ltx', 'txt', 'odt', 'ott',
        'pdb', 'pdf', 'psw', 'rtf', 'sdw', 'stw', 'sxw', 'uot', 'vor', 'wps',
        'bmp', 'emf', 'eps', 'fodg', 'gif', 'jpg', 'met', 'odd', 'otg', 'pbm',
        'pct', 'pgm', 'png', 'ppm', 'ras', 'std', 'svg', 'svm', 'swf', 'sxd',
        'tiff', 'wmf', 'xhtml', 'xpm', 'fodp', 'odg', 'odp', 'otp', 'potm', 'pot',
        'pptx', 'pps', 'ppt', 'pwp', 'sda', 'sdd', 'sti', 'sxi', 'uop', 'csv',
        'dbf', 'dif', 'fods', 'ods', 'xlsx', 'ots', 'pxl', 'sdc', 'slk', 'stc',
        'sxc', 'uos', 'xls', 'xlt',
    ];
}

/**
 * PDF to TXT
 *
 * This function convert files between pdf to txt using the pdftotext
 *
 * @input  => the file that you want to process
 * @output => the file where you want to store the result
 */
function __unoconv_pdf2txt($input, $output)
{
    if (!check_commands('pdftotext')) {
        return;
    }
    ob_passthru("pdftotext -nopgbrk -layout $input $output 2>&1");
    if (file_exists($output)) {
        chmod_protected($output, 0666);
        $freq = count_chars(file_get_contents($output));
        $freq = [array_sum(array_slice($freq, 33, 128 - 33)), array_sum(array_slice($freq, 128))];
        $freq = $freq[1] / max(array_sum($freq), 1);
        if ($freq >= 0.90) {
            unlink($output);
        }
    }
}

/**
 * All to PDF
 *
 * This function convert all formats to pdf using libreoffice
 *
 * @input  => the file that you want to process
 * @output => the file where you want to store the result
 */
function __unoconv_all2pdf($input, $output)
{
    __unoconv_convert($input, $output, 'pdf');
}

/**
 * Convert
 *
 * This function convert between formats using libreoffice
 *
 * @input  => the file that you want to process
 * @output => the file where you want to store the result
 * @format => the desired destination format
 */
function __unoconv_convert($input, $output, $format)
{
    if (!check_commands('soffice')) {
        return;
    }
    $input = realpath($input);
    $output = realpath_protected($output);
    $input2 = get_cache_file($input);
    $fix = (dirname($input) != dirname($input2));
    if ($fix) {
        symlink($input, $input2);
    } else {
        $input2 = $input;
    }
    $outdir = dirname($input2);
    ob_passthru(__exec_timeout("soffice --headless --convert-to $format --outdir $outdir $input2 2>&1"));
    if ($fix) {
        unlink($input2);
    }
    $output2 = str_replace('.' . extension($input2), '.' . $format, $input2);
    if (!file_exists($output2)) {
        return;
    }
    chmod_protected($output2, 0666);
    if ($output != $output2) {
        rename($output2, $output);
    }
}

/**
 * Image to OCR
 *
 * This file uses tesseract to extract all text from the file, if the file
 * is not a tiff image, then is converted to a tiff to be used as input in
 * the tesseract process.
 *
 * @file => the file that you want to process
 */
function __unoconv_img2ocr($file)
{
    if (!check_commands(['convert', 'tesseract'])) {
        return '';
    }
    $type = saltos_content_type($file);
    if ($type != 'image/tiff') {
        $tiff = get_cache_file($file, '.tif');
        //~ if(file_exists($tiff)) unlink($tiff);
        if (!file_exists($tiff)) {
            ob_passthru("convert $file -quality 100 $tiff 2>&1");
            if (!file_exists($tiff)) {
                return '';
            }
        }
        $file = $tiff;
        chmod_protected($tiff, 0666);
    }
    $hocr = get_cache_file($file, '.hocr');
    $html = str_replace('.hocr', '.html', $hocr);
    $txt = str_replace('.hocr', '.txt', $hocr);
    if (file_exists($html)) {
        $hocr = $html;
    }
    //~ if(file_exists($hocr)) unlink($hocr);
    if (!file_exists($hocr)) {
        $base = str_replace(['.hocr', '.html'], '', $hocr);
        ob_passthru(__exec_timeout("tesseract $file $base --psm 1 hocr 2>&1"));
        if (file_exists($html)) {
            $hocr = $html;
        }
        if (file_exists($txt)) {
            unlink($txt);
        }
    }
    if (isset($tiff)) {
        file_put_contents($tiff, '');
        chmod_protected($tiff, 0666);
    }
    if (!file_exists($hocr)) {
        return '';
    }
    chmod_protected($hocr, 0666);
    //~ if(file_exists($txt)) unlink($txt);
    if (!file_exists($txt)) {
        file_put_contents($txt, __unoconv_hocr2txt($hocr));
    }
    chmod_protected($txt, 0666);
    return file_get_contents($txt);
}

/**
 * PDF to OCR
 *
 * This function uses the pdftoppm command to generate one image per page,
 * and then, extract the text of each page to finish the task.
 *
 * @pdf => the file that you want to process
 */
function __unoconv_pdf2ocr($pdf)
{
    if (!check_commands('pdftoppm')) {
        return '';
    }
    // EXTRACT ALL IMAGES FROM PDF
    $root = get_directory('dirs/cachedir') . md5_file($pdf);
    $files = glob("{$root}-*");
    //~ foreach($files as $file) unlink(array_pop($files));
    if (!count($files)) {
        ob_passthru("pdftoppm -r 300 -l 1000 $pdf $root 2>&1");
    }
    // EXTRACT ALL TEXT FROM TIFF
    $files = glob("{$root}-*");
    $result = [];
    foreach ($files as $file) {
        $result[] = __unoconv_img2ocr($file);
        file_put_contents($file, '');
        chmod_protected($file, 0666);
    }
    $result = implode("\n\n", $result);
    return $result;
}

/**
 * Calculate histogram value
 *
 * This function calculates a representative value from a histogram based on given usage thresholds.
 * It finds the highest percentage where at least a certain portion of values and unique values are included.
 *
 * @values => array of values to analyze
 * @usage1 => minimum percentage of total values to include (0-1)
 * @usage2 => minimum percentage of unique values to include (0-1)
 *
 * Returns the calculated representative value
 */
function __unoconv_histogram($values, $usage1, $usage2)
{
    $histo = [];
    foreach ($values as $val) {
        $val = round($val, 0);
        if (!isset($histo[$val])) {
            $histo[$val] = 0;
        }
        $histo[$val]++;
    }
    //~ echo "<pre>";
    //~ arsort($histo);
    //~ print_r($histo);
    //~ echo "</pre>";
    $count1 = count($values);
    $count2 = count($histo);
    $percent = 1;
    $incr = 0.01;
    for (;;) {
        $value = 0;
        $total = 0;
        $used = 0;
        foreach ($histo as $key => $val) {
            if ($val >= $count1 * $percent) {
                $value += $key * $val;
                $total += $val;
                $used++;
            }
        }
        if ($total >= $count1 * $usage1 && $used >= $count2 * $usage2) {
            break;
        }
        $percent -= $incr;
        if ($percent < 0) {
            break;
        }
    };
    $value /= $total;
    return $value;
}

/**
 * Rotate coordinates
 *
 * This function rotates a point around the origin by a given angle in degrees.
 *
 * @posx  => x coordinate of the point
 * @posy  => y coordinate of the point
 * @angle => rotation angle in degrees
 *
 * Returns the array with new x and y coordinates
 */
function __unoconv_rotate($posx, $posy, $angle)
{
    $ang = rad2deg(atan2(floatval($posy), floatval($posx)));
    $mod = sqrt($posx * $posx + $posy * $posy);
    $ang = deg2rad($ang + $angle);
    $posx = $mod * cos($ang);
    $posy = $mod * sin($ang);
    return [$posx, $posy];
}

/**
 * Extract attributes from OCR node
 *
 * This function processes a node from OCR output to extract its attributes,
 * specifically focusing on the bounding box information.
 *
 * @node => the OCR node to process
 *
 * Returns the array containing node ID and bounding box coordinates
 */
function __unoconv_node2attr($node)
{
    if (strpos($node['#attr']['title'], '; ') !== false) {
        $temp = explode('; ', $node['#attr']['title']);
        foreach ($temp as $temp2) {
            if (substr($temp2, 0, 4) == 'bbox') {
                $node['#attr']['title'] = $temp2;
            }
        }
    }
    $temp = explode('_', $node['#attr']['id']);
    $node['#attr']['id'] = $temp[0];
    $temp = array_merge([$node['#attr']['id']], array_slice(explode(' ', $node['#attr']['title']), 1));
    return $temp;
}

/**
 * Extract text value from OCR node
 *
 * This function extracts the text content from an OCR node, handling nested arrays.
 *
 * @node => the OCR node to process
 *
 * Returns the extracted text content
 */
function __unoconv_node2value($node)
{
    while (is_array($node['value'])) {
        $node['value'] = array_pop($node['value']);
    }
    $node['value'] = trim($node['value']);
    return $node['value'];
}

/**
 * Convert OCR lines to character matrix
 *
 * This function converts OCR-detected lines and words into a 2D character matrix
 * for text reconstruction and analysis.
 *
 * @lines  => array of OCR-detected lines and words
 * @width  => width divisor for coordinate normalization
 * @height => height divisor for coordinate normalization
 *
 * Returns the 2D character matrix or index of problematic line if error occurs
 */
function __unoconv_lines2matrix($lines, $width, $height)
{
    $matrix = [];
    $posy = null;
    foreach ($lines as $index => $line) {
        if ($line[0] == 'line') {
            $posy = round((($line[4] + $line[2]) / 2) / $height, 0);
            if (!isset($matrix[$posy])) {
                $matrix[$posy] = [];
            }
        }
        if ($line[0] == 'word') {
            // AS MAKEBOX FEATURE
            if ($line[5] == '') {
                $line[5] = '~';
            }
            // AS DEFAULT FEATURE
            $len = mb_strlen($line[5]);
            $bias = ($line[3] - $line[1]) / ($len * 2);
            $posx = round(($line[1] + $bias) / $width, 0);
            for ($i = 0; $i < $len; $i++) {
                $letter = mb_substr($line[5], $i, 1);
                if (isset($matrix[$posy][$posx])) {
                    if ($letter != '_') {
                        if ($matrix[$posy][$posx] != '_') {
                            return $index;
                        }
                        $matrix[$posy][$posx] = $letter;
                    }
                } else {
                    $matrix[$posy][$posx] = $letter;
                }
                $posx++;
            }
        }
    }
    return $matrix;
}

/**
 * Reorder line coordinates
 *
 * This function reorders the coordinates of a line based on specified positions,
 * used for correcting orientation in OCR results.
 *
 * @line => original line coordinates
 * @pos1 => target position for first coordinate
 * @pos2 => target position for second coordinate
 * @pos3 => target position for third coordinate
 * @pos4 => target position for fourth coordinate
 *
 * Returns the reordered line coordinates
 */
function __unoconv_fixline($line, $pos1, $pos2, $pos3, $pos4)
{
    $temp = $line;
    $line[1] = $temp[$pos1];
    $line[2] = $temp[$pos2];
    $line[3] = $temp[$pos3];
    $line[4] = $temp[$pos4];
    return $line;
}

/**
 * Convert HOCR to plain text
 *
 * This function processes HOCR (HTML OCR) output to extract and reconstruct
 * the text content while maintaining spatial relationships.
 *
 * @hocr => HOCR content to process
 *
 * Returns the extracted plain text
 */
function __unoconv_hocr2txt($hocr)
{
    // LOAD XML
    require_once 'php/lib/import.php';
    $array = __import_xml2array($hocr);
    $array = __array_getnode('html/body', $array);
    // PARTE XML
    $lines = [];
    $words = 0;
    if (is_array($array)) {
        foreach ($array as $page) {
            $lines[] = __unoconv_node2attr($page);
            if (is_array($page['value'])) {
                foreach ($page['value'] as $block) {
                    $lines[] = __unoconv_node2attr($block);
                    if (is_array($block['value'])) {
                        foreach ($block['value'] as $par) {
                            $lines[] = __unoconv_node2attr($par);
                            if (is_array($par['value'])) {
                                foreach ($par['value'] as $line) {
                                    $lines[] = __unoconv_node2attr($line);
                                    if (is_array($line['value'])) {
                                        foreach ($line['value'] as $word) {
                                            $lines[] = array_merge(
                                                __unoconv_node2attr($word),
                                                [__unoconv_node2value($word)]
                                            );
                                            $words++;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    if ($words < 1) {
        return '';
    }
    //~ echo "<pre>".sprintr($lines)."</pre>";
    // COMPUTE ANGLE
    $angles = [];
    $pos1 = null;
    foreach ($lines as $line) {
        if ($line[0] == 'line') {
            $pos1 = null;
        }
        if ($line[0] == 'word') {
            $pos2 = [($line[3] + $line[1]) / 2, ($line[4] + $line[2]) / 2];
            if (is_array($pos1)) {
                $incrx = $pos2[0] - $pos1[0];
                $incry = $pos2[1] - $pos1[1];
                $angles[] = rad2deg(atan2($incry, $incrx));
            }
            $pos1 = $pos2;
        }
    }
    $angle = count($angles) ? __unoconv_histogram($angles, 0.25, 0) : 0;
    //~ echo "<pre>".sprintr(array($angle))."</pre>";
    // APPLY ANGLE CORRECTION
    $quadrant = null;
    foreach ($lines as $index => $line) {
        if ($line[1] != 0 && $line[2] != 0) {
            list($line[1], $line[2]) = __unoconv_rotate($line[1], $line[2], -$angle);
        }
        if ($line[3] != 0 && $line[4] != 0) {
            list($line[3], $line[4]) = __unoconv_rotate($line[3], $line[4], -$angle);
        }
        if ($index == 0) {
            $incrx = $line[3] - $line[1];
            $incry = $line[4] - $line[2];
            if ($incrx >= 0 && $incry >= 0) {
                $quadrant = 0;
            } elseif ($incrx >= 0 && $incry < 0) {
                $quadrant = 1;
            } elseif ($incrx < 0 && $incry < 0) {
                $quadrant = 2;
            } elseif ($incrx < 0 && $incry >= 0) {
                $quadrant = 3;
            }
            //~ echo "<pre>".sprintr(array($incrx,$incry,$quadrant))."</pre>";
        }
        if ($quadrant == 1) {
            $line = __unoconv_fixline($line, 1, 4, 3, 2);
        } elseif ($quadrant == 2) {
            $line = __unoconv_fixline($line, 3, 4, 1, 2);
        } elseif ($quadrant == 3) {
            $line = __unoconv_fixline($line, 3, 2, 1, 4);
        }
        $lines[$index] = $line;
    }
    // COMPUTE MATRIX
    $matrix = null;
    for ($size = 10; $size < 1000; $size += 10) {
        $width = ($lines[0][3] - $lines[0][1]) / $size;
        $height = ($lines[0][4] - $lines[0][2]) / $size;
        $matrix = __unoconv_lines2matrix($lines, $width, $height);
        if (is_array($matrix)) {
            break;
        }
    }
    //~ echo "<pre>".sprintr(array($size,$width,$height))."</pre>";
    if (!is_array($matrix)) {
        return '';
    }
    // MAKE OUTPUT
    $buffer = [];
    $minx = round($lines[0][1] / $width, 0);
    $maxx = round($lines[0][3] / $width, 0);
    $miny = round($lines[0][2] / $height, 0);
    $maxy = round($lines[0][4] / $height, 0);
    for ($y = $miny; $y <= $maxy; $y++) {
        $temp = [];
        for ($x = $minx; $x <= $maxx; $x++) {
            $temp[] = isset($matrix[$y][$x]) ? $matrix[$y][$x] : ' ';
        }
        $buffer[] = implode('', $temp);
    }
    $buffer = implode("\n", $buffer);
    return $buffer;
}

/**
 * Proportional substring extraction
 *
 * This function extracts a substring based on proportional positions relative
 * to a reference length, useful for working with scaled text representations.
 *
 * @string    => input string to extract from
 * @start     => starting position (relative to reference)
 * @length    => length to extract (relative to reference)
 * @reference => reference length for proportional calculation
 *
 * Returns the extracted substring
 */
function __unoconv_substr($string, $start, $length, $reference)
{
    $factor = mb_strlen($string) / $reference;
    $start *= $factor;
    $length *= $factor;
    //~ echo "factor=$factor, start=$start, length=$length<br/>";
    return mb_substr($string, intval($start), intval($length));
}

/**
 * 2D proportional substring extraction
 *
 * This function extracts a 2D region from a text page based on proportional
 * coordinates, maintaining spatial relationships in the extracted content.
 *
 * @page => array of text lines representing the page
 * @x1   => starting x position (relative to x3)
 * @x2   => width to extract (relative to x3)
 * @x3   => reference width for x coordinates
 * @y1   => starting y position (relative to y3)
 * @y2   => height to extract (relative to y3)
 * @y3   => reference height for y coordinates
 *
 * Returns the array of extracted lines
 */
function __unoconv_substr2d($page, $x1, $x2, $x3, $y1, $y2, $y3)
{
    $factor = count($page) / $y3;
    $y1 *= $factor;
    $y2 *= $factor;
    $result = [];
    for ($i = intval($y1); $i < intval($y2); $i++) {
        if (isset($page[$i])) {
            $result[] = __unoconv_substr($page[$i], $x1, $x2, $x3);
        }
    }
    //~ echo "<pre>".sprintr($result)."</pre>";
    return $result;
}

/**
 * Remove margins from text page
 *
 * This function trims empty margins from a text page, removing leading/trailing
 * whitespace and empty lines from the top and bottom.
 *
 * @page => text content to process (multiple lines separated by newlines)
 *
 * Returns the text content with margins removed
 */
function __unoconv_remove_margins($page)
{
    $page = explode("\n", $page);
    $max = 0;
    $min = 0;
    $first = -1;
    $last = -1;
    foreach ($page as $index => $line) {
        $max = max(mb_strlen(rtrim($line)), $max);
        if ($min == 0) {
            $min = $max;
        }
        $min = min(mb_strlen($line) - mb_strlen(ltrim($line)), $min);
        if (trim($line) != '') {
            if ($first == -1) {
                $first = $index;
            } else {
                $last = $index;
            }
        }
    }
    foreach ($page as $index => $line) {
        if ($index < $first) {
            unset($page[$index]);
        } elseif ($index > $last) {
            unset($page[$index]);
        } else {
            $page[$index] = mb_substr($line, $min, $max - $min);
        }
    }
    $page = implode("\n", $page);
    return $page;
}
