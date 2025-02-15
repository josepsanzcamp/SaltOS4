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
 * Array helper module
 *
 * This file contain useful array functions, too we have added some features
 * that historically appear in the import module, they allow to modify arrays
 * using xpath notation, apply filters and patchs or simply, manipulate the
 * data stored in an array of the form row/rows.
 */

/**
 * Null to array converter
 *
 * This function convert all nulls into an array, is intended to be
 * used as helper for example in the glob output, to force to have
 * an array in all cases
 *
 * @arr => the input, generally must to be an array, if a null is passed,
 *         then a void array will be returned
 */
function array_protected($x)
{
    if ($x === null) {
        return [];
    }
    if (is_string($x)) {
        if ($x == '') {
            return [];
        }
        return [$x];
    }
    if (!is_array($x)) {
        return [$x];
    }
    return $x;
}

/**
 * Join attr value
 *
 * This function allow to join the #attr and value to get only an associative
 * array, it is intended to be used to simplify the specification of some elements
 * and to simplify the usage of this elements in the client side
 *
 * @array => the input that can contains an array with #attr and value
 */
function join_attr_value($array)
{
    if (is_attr_value($array)) {
        if (is_string($array['value'])) {
            if (trim($array['value']) == '') {
                $array['value'] = [];
            } else {
                $array['value'] = ['value' => $array['value']];
            }
        }
        $array = array_merge($array['value'], $array['#attr']);
    }
    return $array;
}

/**
 * Get Node helper
 *
 * This function is a helper used to get a node in a xml structure
 *
 * @path  => a path of the desired node
 * @array => the array with nodes of the xml structure
 *
 * Returns the contents of the node of the specified path
 */
function __array_getnode($path, $array)
{
    if (!is_array($path)) {
        $path = explode('/', $path);
    }
    $elem = array_shift($path);
    if (!is_array($array) || !isset($array[$elem])) {
        return null;
    }
    if (count($path) == 0) {
        return $array[$elem];
    }
    return __array_getnode($path, __array_getvalue($array[$elem]));
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
function __array_getvalue($array)
{
    return is_attr_value($array) ? $array['value'] : $array;
}

/**
 * Get Attr helper
 *
 * This function is a helper used to get a attr element if exists of a node structure
 *
 * @elem  => a string representing an element
 * @array => an array containing the node
 *
 * Returns the attr if exists, otherwise null
 */
function __array_getattr($elem, $array)
{
    return is_attr_value($array) && isset($array['#attr'][$elem]) ? $array['#attr'][$elem] : null;
}

/**
 * Add Node helper
 *
 * This function is used to add data into a xml structure
 *
 * @path  => the desired path where do you want to add the data
 * @array => the array with the xml structure
 * @value => the value that do you want to add
 *
 * true if the function can add the data, false otherwise
 */
function __array_addnode($path, &$array, $value)
{
    if (!is_array($path)) {
        $path = explode('/', $path);
    }
    $elem = array_shift($path);
    if (count($path) == 0) {
        set_array($array, $elem, $value);
        return true;
    }
    if (!is_array($array) || !isset($array[$elem])) {
        return false;
    }
    if (is_attr_value($array[$elem])) {
        return __array_addnode($path, $array[$elem]['value'], $value);
    } else {
        return __array_addnode($path, $array[$elem], $value);
    }
}

/**
 * Set Node helper
 *
 * This function is used to set data into a xml structure
 *
 * @path  => the desired path where do you want to put the data,
 * @array => the array with the xml structure
 * @value => the value that do you want to put
 *
 * Returns true if the function can set the value, false otherwise
 */
function __array_setnode($path, &$array, $value)
{
    if (!is_array($path)) {
        $path = explode('/', $path);
    }
    $elem = array_shift($path);
    if (!is_array($array) || !isset($array[$elem])) {
        return false;
    }
    if (count($path) == 0) {
        $array[$elem] = $value;
        return true;
    }
    if (is_attr_value($array[$elem])) {
        return __array_setnode($path, $array[$elem]['value'], $value);
    } else {
        return __array_setnode($path, $array[$elem], $value);
    }
}

/**
 * Del Node helper
 *
 * This function is used to remove data of the xml structure
 *
 * @path  => the desired path where do you want to remove
 * @array => the array with the xml structure
 *
 * Returns true if the function can remove the path, false otherwise
 */
function __array_delnode($path, &$array)
{
    if (!is_array($path)) {
        $path = explode('/', $path);
    }
    $elem = array_shift($path);
    if (!is_array($array) || !isset($array[$elem])) {
        return false;
    }
    if (count($path) == 0) {
        unset($array[$elem]);
        return true;
    }
    if (is_attr_value($array[$elem])) {
        return __array_delnode($path, $array[$elem]['value']);
    } else {
        return __array_delnode($path, $array[$elem]);
    }
}

/**
 * Filter helper
 *
 * This function tries to apply a filter to a tree array, too allow to use
 * the evaluation system to allow to pass as filter an expression like this
 * A=M23
 *
 * @array  => the tree array that you want to apply the filter
 * @filter => the filter to apply
 * @eval   => set to true if you want to enable the eval feature
 */
function __array_filter($array, $filter, $eval = false)
{
    $result = [];
    foreach ($array as $node) {
        if (__array_filter_rec($node, $filter, $eval)) {
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
 * @node   => the tree node that you want to filter
 * @filter => the filter to apply
 * @eval   => set to 1 if you want to enable the eval feature
 * @parent => this parameter is intended to be used internaly by the function
 */
function __array_filter_rec($node, $filter, $eval, $parent = [])
{
    require_once 'php/lib/import.php';
    if (isset($node['row']) && isset($node['rows'])) {
        // Normal filter
        foreach ($node['row'] as $val) {
            if (stripos($val, $filter) !== false) {
                return true;
            }
        }
        // Eval filter
        if ($eval) {
            $vars = array_merge($parent, array_values($node['row']));
            foreach ($vars as $key => $val) {
                $key = __import_col2name($key);
                $$key = $val;
            }
            $result = eval("return $filter;");
        }
        // Recursive call
        foreach ($node['rows'] as $node2) {
            if (
                __array_filter_rec(
                    $node2, $filter, $eval,
                    array_merge($parent, array_values($node['row']))
                )
            ) {
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
            foreach ($vars as $key => $val) {
                $key = __import_col2name($key);
                $$key = $val;
            }
            $result = eval("return $filter;");
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
 * @key   => the xpath where you want to apply the patch
 * @val   => the val that you want to put in the desired xpath
 */
function __array_apply_patch(&$array, $key, $val)
{
    $key = explode('/', $key);
    $key = array_reverse($key);
    array_pop($key);
    __array_apply_patch_rec($array, $key, $val);
}

/**
 * Apply Patch Recursive helper
 *
 * This function is a helper of the previous function and is able to to the
 * same but with recursivity
 *
 * @array => the array that you want to apply the patch
 * @key   => the xpath where you want to apply the patch
 * @val   => the val that you want to put in the desired xpath
 */
function __array_apply_patch_rec(&$array, $key, $val)
{
    $key0 = array_pop($key);
    $key1 = array_pop($key);
    if ($key0 == 'row') {
        if (isset($array['rows'][$key1])) {
            __array_apply_patch_rec($array['rows'][$key1], $key, $val);
        } elseif (isset($array[$key1])) {
            __array_apply_patch_rec($array[$key1], $key, $val);
        } else {
            show_php_error(['phperror' => "Path '$key0' for '$key1' not found"]);
        }
    } elseif ($key0 == 'col') {
        if (isset($array['row']) && isset($array['rows'])) {
            $col = 0;
            foreach ($array['row'] as $key2 => $val2) {
                if ($col == $key1) {
                    $array['row'][$key2] = $val;
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
        show_php_error(['phperror' => "Unknown '$key0' for '$key1'"]);
    }
}

/**
 * Is attr value
 *
 * This function return true if the data argument is an array with #attr and value
 *
 * @array => the array that wants to check
 */
function is_attr_value($array)
{
    return is_array($array) && isset($array['value']) && isset($array['#attr']) && count($array) == 2;
}

/**
 * Arrays to array
 *
 * This function internally implements the old xml_join feature that allow to merge
 * multiple files into one using the fix_key of the keys in the first level as key
 * to join.
 *
 * $args => This function process dynamically all arguments
 *
 * Notes:
 * - This function is derived from the xmlfiles2array that get all files and process
 *   the contents to join with a certain logics
 * - In a commit, this function become to be the main joining function and xmlfiles2array
 *   uses it to do the real job
 */
function arrays2array()
{
    $result = [];
    foreach (func_get_args() as $array) {
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
 * Xpath search array
 *
 * This function is intended to do searches using the xpath notation
 * like list[id=table]/actions, the main idea is that this function can
 * returns an array with all occurrences because the same xpath can choose
 * more that one result
 *
 * @xpath => the string containing the search xpath
 * @array => the array that contains the search data
 */
function xpath_search_array($xpath, $array)
{
    $xpath = explode('/', $xpath);
    $pattern = '/^(\w+)|\[(\w+)=([\w\s]+)\]/';
    $result = [$array];
    while (count($xpath)) {
        $search = array_shift($xpath);
        preg_match_all($pattern, $search, $matches);
        // Remove unused data from matches
        // This improve the iteration of matches[2] and matches[3]
        unset($matches[0]);
        unset($matches[2][0]);
        unset($matches[3][0]);
        $new_result = [];
        foreach ($result as $array) {
            $array = __array_getvalue($array);
            foreach ($array as $key => $val) {
                $found = false;
                if (fix_key($key) == $matches[1][0]) {
                    if (is_attr_value($val)) {
                        $attr = $val['#attr'];
                    } else {
                        $attr = [];
                    }
                    $combine = array_combine($matches[2], $matches[3]);
                    $intersect = array_intersect_assoc($combine, $attr);
                    $found = ($combine == $intersect);
                }
                if ($found) {
                    $new_result[] = $val;
                }
            }
        }
        $result = $new_result;
    }
    return $result;
}

/**
 * Xpath search first
 *
 * This function is intended to returns the first result
 * of the array returned by the xpath_search_first function
 *
 * @xpath => the string containing the search xpath
 * @array => the array that contains the search data
 *
 * Notes:
 *
 * In case of not occurrences, null is returned
 */
function xpath_search_first($xpath, $array)
{
    return xpath_search_array($xpath, $array)[0] ?? null;
}

/**
 * Xpath search first
 *
 * This function is intended to returns the value of the first
 * result obtained from the xpath_search_first function
 *
 * @xpath => the string containing the search xpath
 * @array => the array that contains the search data
 *
 * Notes:
 *
 * In case of not occurrences, null is returned
 */
function xpath_search_first_value($xpath, $array)
{
    return __array_getvalue(xpath_search_first($xpath, $array));
}

/**
 * Array transpose
 *
 * This function returns a transposed array, intended to be used
 * when needs an array of cols instead of rows, for example, ideal
 * to be used in the excel widget
 *
 * The expected output must to be the same input array but swaping
 * the first and second indeces level, in other words, this function
 * is able to convert arrays from A*B to B*A dimensions
 *
 * @input => the input array
 */
function array_transpose($input)
{
    $output = [];
    foreach ($input as $key => $val) {
        foreach ($val as $key2 => $val2) {
            if (!isset($output[$key2])) {
                $output[$key2] = [];
            }
            $output[$key2][$key] = $val2;
        }
    }
    return $output;
}

/**
 * Array lowercase
 *
 * This function do the same that strtolower but apply it to all array values
 *
 * @array => the array that you want to convert to lower case
 */
function array_lowercase($array)
{
    foreach ($array as $key => $val) {
        $array[$key] = strtolower($val);
    }
    return $array;
}

/**
 * Array key lowercase
 *
 * This function do the same that the array_lowercase but only apply to all
 * keys of the array
 *
 * @array => the array that you want to convert to lower case
 */
function array_key_lowercase($array)
{
    $keys = array_lowercase(array_keys($array));
    $values = array_values($array);
    $array = array_combine($keys, $values);
    return $array;
}

/**
 * Array key search
 *
 * This function allow to search keys in the array using a case insensitive search
 *
 * @needed => the desired key used in the case insensitive search
 * @array  => the array where do you want to found the key
 *
 * Notes:
 *
 * In case of not found the desired key, the original needed param is returned
 */
function array_key_search($needed, $array)
{
    foreach ($array as $key => $val) {
        if (strcasecmp($key, $needed) == 0) {
            return $key;
        }
    }
    return $needed;
}

/**
 * Explode With Quotes
 *
 * This function tries to do the same things that the original explode but add
 * the quotes feature, don't break an string contained in a single or double
 * quotes, this allow to implement features like a search that forces specific
 * strings with spaces
 *
 * @separator => the delimiter character used in the explode feature
 * @str       => the string that you want to explode
 * @limit     => the number of elements that can contains the result
 */
function explode_with_quotes($separator, $str, $limit = 0)
{
    $result = [];
    $len = strlen($str);
    $ini = 0;
    $count = 0;
    $single = 0;
    $double = 0;
    for ($i = 0; $i < $len; $i++) {
        $letter = $str[$i];
        if ($letter == "'") {
            $single = ($single + 1) % 2;
        } elseif ($letter == '"') {
            $double = ($double + 1) % 2;
        }
        if ($letter == $separator && $single == 0 && $double == 0) {
            if ($limit > 0 && $count == $limit - 1) {
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
    if ($i != $ini) {
        $result[] = substr($str, $ini, $i - $ini);
    }
    return $result;
}

/**
 * Grep helper
 *
 * This function emulates the grep command, is able to invert the pattern
 * selection and returns the same array with the grep applied, tries to do
 * the grep ignoring case and ignoring extended chars and is able to search
 * words ignoring accents
 *
 * @input   => the input array
 * @pattern => the search pattern
 * @invert  => default to false to search, true to invert the selection
 */
function array_grep($input, $pattern, $invert = false)
{
    $pattern = iconv('UTF-8', 'ASCII//TRANSLIT', $pattern);
    foreach ($input as $key => $val) {
        $val = iconv('UTF-8', 'ASCII//TRANSLIT', $val);
        $pos = stripos($val, $pattern);
        if (!$invert && $pos === false) {
            unset($input[$key]);
        } elseif ($invert && $pos !== false) {
            unset($input[$key]);
        }
    }
    $input = array_values($input);
    return $input;
}
