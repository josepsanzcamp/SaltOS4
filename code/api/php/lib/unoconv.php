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
        if ($type === 'application/pdf') {
            copy($input, $output);
        } elseif (
            (in_array($ext, __unoconv_list(), true) && !in_array($type0, ['audio', 'video'], true)) ||
            in_array($type0, ['text', 'message', 'image'], true)
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
        if (in_array($type, ['text/plain', 'application/json'], true)) {
            copy($input, $output);
        } elseif ($type === 'text/html') {
            file_put_contents($output, html2text(file_get_contents($input)));
        } elseif ($type === 'application/pdf') {
            __unoconv_pdf2txt($input, $output);
            if (!file_exists($output) || trim(file_get_contents($output)) === '') {
                file_put_contents($output, __unoconv_pdf2ocr($input));
            }
        } elseif (
            (in_array($ext, __unoconv_list(), true) && !in_array($type0, [
                'image', 'audio', 'video',
            ], true)) ||
            in_array($type0, ['text', 'message'], true)
        ) {
            $pdf = get_cache_file($input, '.pdf');
            if (!file_exists($pdf)) {
                __unoconv_all2pdf($input, $pdf);
            }
            if (file_exists($pdf)) {
                chmod_protected($pdf, 0666);
                __unoconv_pdf2txt($pdf, $output);
                if (!file_exists($output) || trim(file_get_contents($output)) === '') {
                    file_put_contents($output, __unoconv_pdf2ocr($pdf));
                }
            }
        } elseif ($type0 === 'image') {
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
    $fix = (dirname($input) !== dirname($input2));
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
    if ($output !== $output2) {
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
    if ($type !== 'image/tiff') {
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
 * Used by __unoconv_hocr2txt to find the dominant reading-angle of a page from many noisy per-word
 * angle samples: it rounds each value to an integer bin, then starts by requiring a bin to contain
 * 100% of the samples to be considered "the" value, and relaxes that requirement by 1% at a time
 * until enough samples (usage1) and/or enough distinct bins (usage2) are covered. The qualifying
 * bins are then combined as a frequency-weighted average. This behaves like a robust mode/average
 * that ignores stray outlier values instead of being skewed by them.
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
        $val = (int)round($val, 0);
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
 * Implemented via polar coordinates (atan2 + hypot-like modulus) instead of the usual
 * sin/cos rotation matrix: convert the point to an angle+distance from the origin, add
 * the rotation angle, then convert back to cartesian. Mathematically equivalent, just
 * written this way in the original implementation.
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
 * hOCR nodes carry a title attribute that can list several "; " separated properties,
 * e.g. `bbox 10 20 30 40; x_wconf 95` on words. Only the bbox part is kept, the rest
 * (like the OCR confidence) is discarded.
 *
 * hOCR ids are of the form `word_1_3`, `line_1_2`, `par_1`, `block_1`, `page_1`: the
 * prefix before the first "_" identifies the element type (word/line/par/block/page),
 * the numbers are just hOCR's own counters and are not used here. Keeping only that
 * prefix repurposes the "id" as a type tag: the rest of the hocr2txt pipeline branches
 * on this value (`$line[0] === 'word'`, `'line'`, ...) instead of on the original id.
 *
 * @node => the OCR node to process
 *
 * Returns [type, x1, y1, x2, y2], where type is 'page'|'block'|'par'|'line'|'word'
 * and x1,y1,x2,y2 are the bbox corners taken from the title attribute
 */
function __unoconv_node2attr($node)
{
    if (strpos($node['#attr']['title'], '; ') !== false) {
        $temp = explode('; ', $node['#attr']['title']);
        foreach ($temp as $temp2) {
            if (substr($temp2, 0, 4) === 'bbox') {
                $node['#attr']['title'] = $temp2;
            }
        }
    }
    $temp = explode('_', $node['#attr']['id']);
    $node['#attr']['id'] = $temp[0];
    // Drop the "bbox" word itself from the title, keep only the 4 numbers
    $temp = array_merge([$node['#attr']['id']], array_slice(explode(' ', $node['#attr']['title']), 1));
    return $temp;
}

/**
 * Extract text value from OCR node
 *
 * This function extracts the text content from an OCR node, handling nested arrays.
 *
 * The XML-to-array parser (see __import_xml2array/struct2array) turns any nested inline
 * markup inside a word (e.g. hOCR wrapping part of the word text in a sub-tag) into a
 * nested array instead of a plain string. This walks down through that nesting via
 * array_pop (last child) until a scalar is reached, which is then trimmed.
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
 * $lines is the flat, type-tagged list built by __unoconv_hocr2txt (see __unoconv_node2attr):
 * every 'line' entry that appears sets the "current row" for the 'word' entries that follow it,
 * until the next 'line' entry changes it again. $width/$height are the pixel size of one grid
 * cell (page bbox divided by the "size" being tried by the caller's loop), used to convert
 * pixel bounding boxes into row/column indexes on the character grid.
 *
 * For each word: if hOCR gave no text at all (a detected word box with empty content), it is
 * replaced by a single '~' placeholder so the box still leaves a visible trace in the output
 * ("makebox" case) instead of silently disappearing. Otherwise, the word's pixel width is
 * divided by its character count to estimate a per-character width (`$bias` centers the first
 * character instead of aligning it to the box's left edge), and each subsequent character just
 * advances one column — this assumes roughly monospaced character widths within a word, which
 * is only an approximation but good enough at typical OCR resolutions.
 *
 * Collision handling is what drives the grid-size search in __unoconv_hocr2txt: '_' is treated
 * as a transparent filler (never overwrites, never conflicts), but if two different non-'_'
 * characters land on the same cell, the grid is too coarse for this page, and the function
 * aborts by returning the offending $index (an int) instead of the matrix (an array) — the
 * caller detects this with is_array() and retries with a finer grid.
 *
 * @lines  => array of OCR-detected lines and words
 * @width  => width divisor for coordinate normalization
 * @height => height divisor for coordinate normalization
 *
 * Returns the 2D character matrix, or the index of the colliding line/word if the current
 * grid resolution is too coarse to place all characters without overlap
 */
function __unoconv_lines2matrix($lines, $width, $height)
{
    $matrix = [];
    $posy = null;
    foreach ($lines as $index => $line) {
        if ($line[0] === 'line') {
            $posy = (int)round((($line[4] + $line[2]) / 2) / $height, 0);
            if (!isset($matrix[$posy])) {
                $matrix[$posy] = [];
            }
        }
        if ($line[0] === 'word') {
            // AS MAKEBOX FEATURE
            if ($line[5] === '') {
                $line[5] = '~';
            }
            // AS DEFAULT FEATURE
            $len = mb_strlen($line[5]);
            $bias = ($line[3] - $line[1]) / ($len * 2);
            $posx = (int)round(($line[1] + $bias) / $width, 0);
            for ($i = 0; $i < $len; $i++) {
                $letter = mb_substr($line[5], $i, 1);
                if (isset($matrix[$posy][$posx])) {
                    if ($letter !== '_') {
                        if ($matrix[$posy][$posx] !== '_') {
                            // Real collision: two different characters want the same cell,
                            // signal failure to the caller so it retries with a finer grid
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
 * Rotating a bbox's two corners independently (see __unoconv_rotate) can leave the corners
 * in the "wrong" order once the dominant angle is close to a 90/180/270 degree multiple: what
 * was (x1,y1)=top-left/(x2,y2)=bottom-right may come out flipped on one or both axes. This
 * takes the 4 original values [x1,y1,x2,y2] (indexes 1-4 of a __unoconv_node2attr line) and
 * permutes them according to the quadrant detected by the caller, so every node ends up with
 * a consistent x1<x2, y1<y2 orientation again.
 *
 * @line => original line coordinates
 * @pos1 => source index (into $line) for the corrected x1
 * @pos2 => source index (into $line) for the corrected y1
 * @pos3 => source index (into $line) for the corrected x2
 * @pos4 => source index (into $line) for the corrected y2
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
 * tesseract's hOCR output gives every page/block/paragraph/line/word a pixel bounding box,
 * but no ready-made plain text layout. Simply concatenating word texts would lose all spatial
 * structure (columns, tables, indentation). This function rebuilds that structure by placing
 * every word onto a 2D grid of characters according to its pixel position, so the resulting
 * plain text roughly preserves the visual layout of the page. Overview of the steps below,
 * each one marked in the code:
 *
 * 1. LOAD XML: parse the hOCR (which is XHTML) into a PHP array and drill down to <body>.
 * 2. PARSE XML: flatten the page/block/par/line/word tree into one linear, type-tagged list
 *    (see __unoconv_node2attr), since the algorithm doesn't need the hierarchy, only bboxes
 *    and reading order. Bail out early if no words were found.
 * 3. COMPUTE ANGLE: measure the direction (angle) between consecutive word centers within
 *    each line, then use __unoconv_histogram to find the dominant angle across the page,
 *    i.e. how much the whole page appears to be rotated/skewed.
 * 4. APPLY ANGLE CORRECTION: rotate every bbox by the opposite of that angle, and normalize
 *    corner order in case the rotation flipped the page into a different quadrant (see
 *    __unoconv_fixline).
 * 5. COMPUTE MATRIX: try placing all words onto a character grid (see __unoconv_lines2matrix),
 *    starting from a coarse grid and refining it until no two characters collide.
 * 6. MAKE OUTPUT: walk the final grid row by row/column by column (using the page's own bbox,
 *    in grid units, as the boundaries) and join it into a plain text block, using spaces for
 *    empty cells.
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
    // PARSE XML: walk page > block > par > line > word and flatten everything into $lines,
    // tagging each entry with its type via __unoconv_node2attr (see that function for why the
    // hOCR "id" ends up being reused as a type tag instead of an identifier)
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
    // COMPUTE ANGLE: for each line, walk its words in order and measure the angle between
    // the centers of consecutive words (this is the actual "reading direction" on the page,
    // which will differ from 0 degrees if the source was scanned skewed/rotated). $pos1 is
    // reset to null on every 'line' entry so angles are only measured within the same line,
    // never across a line break.
    $angles = [];
    $pos1 = null;
    foreach ($lines as $line) {
        if ($line[0] === 'line') {
            $pos1 = null;
        }
        if ($line[0] === 'word') {
            $pos2 = [($line[3] + $line[1]) / 2, ($line[4] + $line[2]) / 2];
            if (is_array($pos1)) {
                $incrx = $pos2[0] - $pos1[0];
                $incry = $pos2[1] - $pos1[1];
                $angles[] = rad2deg(atan2($incry, $incrx));
            }
            $pos1 = $pos2;
        }
    }
    // Robust "dominant angle" of the page: at least 25% of all word-to-word angle samples
    // must agree (see __unoconv_histogram) to avoid being thrown off by a few noisy words
    $angle = count($angles) ? __unoconv_histogram($angles, 0.25, 0) : 0;
    //~ echo "<pre>".sprintr(array($angle))."</pre>";
    // APPLY ANGLE CORRECTION: rotate every node's bbox corners by -$angle to undo the page
    // skew. Coordinates exactly at 0 are left untouched since rotating the origin point is
    // meaningless (angle undefined) and would otherwise just introduce rounding noise.
    $quadrant = null;
    foreach ($lines as $index => $line) {
        if ($line[1] !== 0 && $line[2] !== 0) {
            list($line[1], $line[2]) = __unoconv_rotate($line[1], $line[2], -$angle);
        }
        if ($line[3] !== 0 && $line[4] !== 0) {
            list($line[3], $line[4]) = __unoconv_rotate($line[3], $line[4], -$angle);
        }
        if ($index === 0) {
            // $lines[0] is always the page node itself (first thing pushed above): use the
            // direction of its own diagonal, after rotation, to detect whether the rotation
            // effectively flipped the page into a different quadrant (this can happen when
            // the dominant angle is near a 90/180/270 degree multiple), independently of the
            // fine-grained angle correction already applied above.
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
        // Quadrant 0 is already in the expected x1<x2, y1<y2 order and needs no fix.
        // Quadrants 1/2/3 permute the corners (see __unoconv_fixline) to bring every node
        // back to that same order, applied uniformly to page/block/par/line/word alike.
        if ($quadrant === 1) {
            $line = __unoconv_fixline($line, 1, 4, 3, 2);
        } elseif ($quadrant === 2) {
            $line = __unoconv_fixline($line, 3, 4, 1, 2);
        } elseif ($quadrant === 3) {
            $line = __unoconv_fixline($line, 3, 2, 1, 4);
        }
        $lines[$index] = $line;
    }
    // COMPUTE MATRIX: try increasingly finer grids (size = number of cells across the page
    // width/height) starting from a coarse 10x10-ish grid, until __unoconv_lines2matrix can
    // place every character without two different characters landing on the same cell. A
    // coarser grid is preferred when it works, since it keeps the output more compact/readable.
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
    // MAKE OUTPUT: the page bbox (in the same grid units used above) defines the row/column
    // bounds; any cell without a character placed in it becomes a plain space.
    $buffer = [];
    $minx = (int)round($lines[0][1] / $width, 0);
    $maxx = (int)round($lines[0][3] / $width, 0);
    $miny = (int)round($lines[0][2] / $height, 0);
    $maxy = (int)round($lines[0][4] / $height, 0);
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
 * $string is treated as if it were exactly $reference characters long (e.g. a 0-100
 * scale representing percentages across a line), and $start/$length are expressed in
 * that same scale. factor = mb_strlen($string) / $reference converts them to real
 * character offsets before delegating to mb_substr(). This exists because the plain
 * text pages built by __unoconv_hocr2txt don't have a fixed character width: the grid
 * resolution chosen by its "COMPUTE MATRIX" retry loop can vary between runs/pages, so
 * a caller that wants "roughly the right half of this line" cannot rely on fixed
 * absolute character offsets and needs proportional ones instead.
 *
 * Important: $length here is a LENGTH, exactly like PHP's own mb_substr($string, $start,
 * $length) - it is added to $start to know how many characters to take, it is NOT an end
 * position. Compare with __unoconv_substr2d, whose y1/y2 use a different (start,end)
 * convention for rows.
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
 * Crops a page (an array of text lines, as returned by __unoconv_hocr2txt) to a
 * proportional rectangle, e.g. "rows 30-70 out of 100, columns 30-70 out of 100" to
 * grab roughly the center of a page while ignoring scanned margins/headers/footers.
 *
 * The two axes are NOT handled the same way, which is easy to miss because the call
 * signature looks symmetric:
 * - Rows (y1,y2,y3): factor = count($page) / $y3, then y1/y2 are scaled and used
 *   directly as a real [start, end) range in the for loop below.
 * - Columns (x1,x2,x3): forwarded as-is to __unoconv_substr($page[$i], $x1, $x2, $x3),
 *   where the second value is a LENGTH, not an end column (see __unoconv_substr).
 * So $y2 is "where to stop" but $x2 is "how many (proportional) characters to take from
 * $x1" - passing the same pair of numbers for both (e.g. 30/70) does not crop the same
 * relative region on both axes; getting a symmetric crop on x requires passing a length
 * ($x2 - $x1), not an end position.
 *
 * @page => array of text lines representing the page
 * @x1   => starting x position (relative to x3)
 * @x2   => width to extract (relative to x3) - forwarded as a LENGTH, see above
 * @x3   => reference width for x coordinates
 * @y1   => starting row (relative to y3)
 * @y2   => ending row, exclusive (relative to y3)
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
 * Two bounds are computed in a single pass: $max is the length of the longest line once
 * right-trimmed (the right edge of the content), and $min is the smallest leading-whitespace
 * count seen across all lines (the left edge). Together they crop every line to
 * mb_substr($line, $min, $max - $min) below. $first/$last track the first and last
 * non-blank line indexes, and anything outside that range is dropped entirely (empty
 * lines at the top/bottom of the page).
 *
 * `if ($min === 0) { $min = $max; }` bootstraps $min on the first line, where it would
 * otherwise be compared against its placeholder starting value of 0 instead of a proper
 * "nothing seen yet" sentinel. Because 0 is also a legitimate indent (a line with no
 * leading whitespace), this same check re-triggers on any later line whose *cumulative*
 * $min had already reached exactly 0, resetting it back up to that line's $max before
 * re-narrowing it — which can discard an already-found zero-indent minimum. In practice
 * this tends to self-correct if further unindented lines follow, but a page where an
 * unindented line is followed only by indented ones can end up with $min > 0 even though
 * a real 0-indent line exists earlier on the page.
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
        if ($min === 0) {
            $min = $max;
        }
        $min = min(mb_strlen($line) - mb_strlen(ltrim($line)), $min);
        if (trim($line) !== '') {
            if ($first === -1) {
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
