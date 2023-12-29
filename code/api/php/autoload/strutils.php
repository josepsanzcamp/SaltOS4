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
 * String utils helper module
 *
 * This fie contains useful functions related to strings manipulations, suck as get the test in
 * UTF8, remove bad chars, of apply filters to the text, manipulate texts of other related tasks
 * with strings
 */

/**
 * Remove Bar Chars
 *
 * This function removes chars from keycodes 0 to 31 except 9, 10, 13 (tab,
 * newline, return)
 *
 * @temp => input string that you want to fix
 * @pad  => padding string used as replacement for bar chars (void by default)
 */
function remove_bad_chars($temp, $pad = "")
{
    static $bad_chars = null;
    if ($bad_chars === null) {
        $bad_chars = [
            0, 1, 2, 3, 4, 5, 6, 7, 8, 11, 12, 14, 15, 16, 17, 18,
            19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31,
        ];
        foreach ($bad_chars as $key => $val) {
            $bad_chars[$key] = chr($val);
        }
    }
    $temp = str_replace($bad_chars, $pad, $temp);
    return $temp;
}

/**
 * Encode Bar Chars
 *
 * This function tries to replace accender chars and other extended chars into
 * an ascii chars, to do it, they define an array with the pairs of chars to
 * do a quick replace, too is converted all to lower and are removed all chars
 * that are out of range (valid range are from 0-9 and from a-z), the function
 * allow to specify an extra parameter to add extra chars that must to be
 * allowed in the output, all other chars will be converted to the padding
 * argument, as a bonus extra, all padding repetitions will be removed to
 * only allow one pading char at time
 *
 * @cad   => the input string to encode
 * @pad   => the padding char using to replace the bar chars
 * @extra => the list of chars allowed to appear in the output
 */
function encode_bad_chars($cad, $pad = "_", $extra = "")
{
    $orig = [
        "á", "à", "ä", "â", "é", "è", "ë", "ê", "í", "ì", "ï", "î",
        "ó", "ò", "ö", "ô", "ú", "ù", "ü", "û", "ñ", "ç",
        "Á", "À", "Ä", "Â", "É", "È", "Ë", "Ê", "Í", "Ì", "Ï", "Î",
        "Ó", "Ò", "Ö", "Ô", "Ú", "Ù", "Ü", "Û", "Ñ", "Ç",
    ];
    $dest = [
        "a", "a", "a", "a", "e", "e", "e", "e", "i", "i", "i", "i",
        "o", "o", "o", "o", "u", "u", "u", "u", "n", "c",
        "a", "a", "a", "a", "e", "e", "e", "e", "i", "i", "i", "i",
        "o", "o", "o", "o", "u", "u", "u", "u", "n", "c",
    ];
    $cad = str_replace($orig, $dest, $cad);
    $cad = strtolower($cad);
    $len = strlen($cad);
    for ($i = 0; $i < $len; $i++) {
        $letter = $cad[$i];
        $replace = 1;
        if ($letter >= "a" && $letter <= "z") {
            $replace = 0;
        }
        if ($letter >= "0" && $letter <= "9") {
            $replace = 0;
        }
        if (strpos($extra, $letter) !== false) {
            $replace = 0;
        }
        if ($replace) {
            $cad[$i] = $pad;
        }
    }
    $cad = prepare_words($cad, $pad);
    return $cad;
}

/**
 * Prepare Words
 *
 * This function allow to prepare words removing repetitions in the padding char
 *
 * @cad => the input string to prepare
 * @pad => the padding char using to replace the repetitions
 *
 * Notes:
 *
 * Apart of remove repetitions of the padding char, the function will try to
 * remove padding chars in the start and in the end of the string
 */
function prepare_words($cad, $pad = " ")
{
    $count = 1;
    while ($count) {
        $cad = str_replace($pad . $pad, $pad, $cad, $count);
    }
    $len = strlen($pad);
    if (substr($cad, 0, $len) == $pad) {
        $cad = substr($cad, $len);
    }
    if (substr($cad, -$len, $len) == $pad) {
        $cad = substr($cad, 0, -$len);
    }
    return $cad;
}

/**
 * Sprintr
 *
 * This function is an improved version of the print_r, allow to convert an
 * array into a string removing some extra lines that not contain information,
 * lines that contains only contains an open or close parenthesis, or nothing,
 * are removed, optimizing the output string
 *
 * @array => the array that do you want to convert into string
 */
function sprintr($array)
{
    $buffer = print_r($array, true);
    $buffer = explode("\n", $buffer);
    foreach ($buffer as $key => $val) {
        if (in_array(trim($val), ["(", ")", ""])) {
            unset($buffer[$key]);
        }
    }
    $buffer = implode("\n", $buffer) . "\n";
    return $buffer;
}

/**
 * Get Unique ID MD5
 *
 * This function returns an unique hash using the random generator
 */
function get_unique_id_md5()
{
    return md5(uniqid(strval(rand()), true));
}

/**
 * Intelligence Cut
 *
 * This function allow to cut text by searching spaces to prevent to break words
 *
 * @txt => the text that you want to cut
 * @max => the size of the expected output text
 * @end => the suffix added if the text is cutted
 */
function intelligence_cut($txt, $max, $end = "...")
{
    $len = strlen($txt);
    if ($len > $max) {
        while ($max > 0 && $txt[$max] != " ") {
            $max--;
        }
        if ($max == 0) {
            while ($max < $len && $txt[$max] != " ") {
                $max++;
            }
        }
        if ($max > 0) {
            if (in_array($txt[$max - 1], [",", ".", "-", "("])) {
                $max--;
            }
        }
        $preview = ($max == $len) ? $txt : substr($txt, 0, $max) . $end;
    } else {
        $preview = $txt;
    }
    return $preview;
}

/**
 * Normalize Value
 *
 * This function allow to detect the last letter to detect what magnitude is
 * using (K, M or G) and multiply the numeric part by the needed factor to
 * get the number without factor
 *
 * @value => the string that contain the number, for example "123k"
 */
function normalize_value($value)
{
    $number = intval(substr($value, 0, -1));
    $letter = strtoupper(substr($value, -1, 1));
    if ($letter == "K") {
        $value = $number * 1024;
    }
    if ($letter == "M") {
        $value = $number * 1024 * 1024;
    }
    if ($letter == "G") {
        $value = $number * 1024 * 1024 * 1024;
    }
    return $value;
}

/**
 * HTML to Text
 *
 * This function uses the html2text roundcube function to convert html to
 * plain text, this code have the issue that requires the error_reporting(0)
 * because it have a lot of errors causes by use nondefined variables, for
 * example
 *
 * @html => the html code that you want to convert to plain text
 */
function html2text($html)
{
    require_once "core/lib/roundcube/rcube_html2text.php";
    $obj = new rcube_html2text($html);
    $text = $obj->get_text();
    return $text;
}

/**
 * Get UTF-8
 *
 * This function returns the string codified in a UTF-8 encoding
 *
 * @str => the input string that you want to covnert to UTF-8
 */
function getutf8($str)
{
    if ($str != "" && !mb_check_encoding($str, "UTF-8")) {
        $str = mb_convert_encoding($str, "UTF-8", mb_detect_order());
    }
    return $str;
}

/**
 * Words Exists
 *
 * This function check that all words exists in the buffer
 *
 * @words  => the string that contains words separated by spaces
 * @buffer => the string where we must to found the words
 */
function words_exists($words, $buffer)
{
    if (!is_array($words)) {
        $words = explode(" ", $words);
    }
    foreach ($words as $word) {
        if (stripos($buffer, $word) === false) {
            return false;
        }
    }
    return true;
}

/**
 * String Replace Assoc
 *
 * This function do the same that str_replace, but using only one associative
 * array, using the keys as search and the values as replace, intended only
 * to do more prerry the code
 *
 * @array => the associative array with the pairs keys vals
 * @cad   => the string that you want to apply the replacement
 */
function str_replace_assoc($array, $cad)
{
    return str_replace(array_keys($array), array_values($array), $cad);
}

/**
 * Get Part From String
 *
 * This function explodes de input using delim and returns the element
 * of the index position
 *
 * @input => the string that you want to cut in parts
 * @delim => the delimiter char used to cut in parts
 * @index => the index that you want to request of the explode result
 */
function get_part_from_string($input, $delim, $index)
{
    $temp = explode($delim, $input);
    return $temp[$index] ?? "";
}

/**
 * Get Unique Token
 *
 * This function returns a string with a new and unique token
 */
function get_unique_token()
{
    return implode("-", [
        bin2hex(random_bytes(4)),
        bin2hex(random_bytes(2)),
        bin2hex(random_bytes(2)),
        bin2hex(random_bytes(2)),
        bin2hex(random_bytes(6)),
    ]);
}
