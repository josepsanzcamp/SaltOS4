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
 * Array to XML helper module
 *
 * This fie is a part of the most old files of the SaltOS, accomplish the task to convert an array
 * to an XML string, currently it is little used because the most important module compared to this
 * is the inverse xml2array module
 */

/**
 * Check Node Name
 *
 * This function acts as helper of the array2xml function, is intended to
 * return if the node name is valid
 *
 * @name => the name that you want to validate
 */
function __array2xml_check_node_name($name)
{
    try {
        new DOMElement(":$name");
        return true;
    } catch (DOMException $e) {
        return false;
    }
}

/**
 * Check Attr Name
 *
 * This function acts as helper of the array2xml function, is intended to
 * return if the attribute name is valid
 *
 * @name => the name that you want to validate
 */
function __array2xml_check_node_attr($name)
{
    try {
        new DOMAttr($name);
        return true;
    } catch (DOMException $e) {
        return false;
    }
}

/**
 * Write Nodes array2xml helper
 *
 * This function acts as helper of the array2xml function, is intended to
 * return a string with the tree array
 *
 * @array => the tree array that you want to convert to XML
 * @level => can be null to minify the output zero to indent the XML contents
 */
function __array2xml_write_nodes(&$array, $level = null)
{
    if ($level === null) {
        $prefix = '';
        $postfix = '';
    } else {
        $prefix = str_repeat(' ', 4 * $level);
        $postfix = "\n";
        $level++;
    }
    $buffer = '';
    foreach ($array as $key => $val) {
        $key = fix_key($key);
        if (!__array2xml_check_node_name($key)) {
            show_php_error(['phperror' => "Invalid XML tag name '$key'"]);
        }
        $attr = '';
        if (is_attr_value($val)) {
            $attr = [];
            foreach ($val['#attr'] as $key2 => $val2) {
                $key2 = fix_key($key2);
                if (!__array2xml_check_node_attr($key2)) {
                    show_php_error(['phperror' => "Invalid XML attr name '$key2'"]);
                }
                $val2 = str_replace('&', '&amp;', strval($val2));
                $attr[] = "$key2=\"$val2\"";
            }
            $attr = ' ' . implode(' ', $attr);
            $val = $val['value'];
        }
        if (is_array($val)) {
            $buffer .= "$prefix<$key$attr>$postfix";
            $buffer .= __array2xml_write_nodes($val, $level);
            $buffer .= "$prefix</$key>$postfix";
        } else {
            $val = remove_bad_chars(strval($val));
            if (strpos($val, '<') !== false || strpos($val, '&') !== false) {
                $count = 1;
                while ($count) {
                    $val = str_replace(['<![CDATA[', ']]>'], '', $val, $count);
                }
                $val = "<![CDATA[$val]]>";
            }
            if ($val != '') {
                $buffer .= "$prefix<$key$attr>$val</$key>$postfix";
            } else {
                $buffer .= "$prefix<$key$attr/>$postfix";
            }
        }
    }
    return $buffer;
}

/**
 * Array to XML
 *
 * This function returns a string with the contents of array converted into a XML
 * language file, to do it, uses some helpers as __array2xml_* functions
 *
 * @array  => the array that contains the tree structure that you want to convert to XML
 * @indent => a boolean to enable or disable the indent (the old minify) feature
 */
function array2xml($array, $indent = false)
{
    $buffer = __array2xml_write_nodes($array, $indent ? 0 : null);
    return $buffer;
}
