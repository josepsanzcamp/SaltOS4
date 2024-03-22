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
        if ($x == "") {
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
 * Join for array
 *
 * This function allow to join the #attr and value to get only an associative
 * array, it is intended to be used to simplify the specification of some elements
 * and to simplify the usage of this elements in the client side
 *
 * @array => the input that can contains an array with #attr and value
 */
function join4array($array)
{
    if (is_array($array) && isset($array["value"]) && isset($array["#attr"])) {
        if (is_string($array["value"])) {
            if (trim($array["value"]) == "") {
                $array["value"] = [];
            } else {
                $array["value"] = ["value" => $array["value"]];
            }
        }
        $array = array_merge($array["#attr"], $array["value"]);
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
        $path = explode("/", $path);
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
    return (is_array($array) && isset($array["value"]) && isset($array["#attr"])) ? $array["value"] : $array;
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
    if (
        !is_array($array) || !isset($array["#attr"]) ||
        !is_array($array["#attr"]) || !isset($array["#attr"][$elem])
    ) {
        return null;
    }
    return $array["#attr"][$elem];
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
        return __array_addnode($path, $array[$elem]["value"], $value);
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
        return __array_setnode($path, $array[$elem]["value"], $value);
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
        return __array_delnode($path, $array[$elem]["value"]);
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
            //~ capture_next_error();
            $result = eval_protected($filter, $vars);
            //~ $error = get_clear_error();
            //~ if ($result && !$error) {
                //~ return true;
            //~ }
        }
        // Recursive call
        foreach ($node["rows"] as $node2) {
            if (
                __array_filter_rec(
                    $node2, $filter, $eval,
                    array_merge($parent, array_values($node["row"]))
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
            $keys = array_keys($vars);
            foreach ($keys as $key => $val) {
                $keys[$key] = __import_col2name($val);
            }
            $vars = array_combine($keys, $vars);
            //~ capture_next_error();
            $result = eval_protected($filter, $vars);
            //~ $error = get_clear_error();
            //~ if ($result && !$error) {
                //~ return true;
            //~ }
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
    $key = explode("/", $key);
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
    if ($key0 == "row") {
        if (isset($array["rows"][$key1])) {
            __array_apply_patch_rec($array["rows"][$key1], $key, $val);
        } elseif (isset($array[$key1])) {
            __array_apply_patch_rec($array[$key1], $key, $val);
        } else {
            show_php_error(["phperror" => "Path '{$key0}' for '{$key1}' not found"]);
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
        show_php_error(["phperror" => "Unknown '{$key0}' for '{$key1}'"]);
    }
}
