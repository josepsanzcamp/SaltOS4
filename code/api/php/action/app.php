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
 * Application action
 *
 * This file implements the app action, requires a GET REST request
 * and the order of the elements are:
 *
 * @1 => the app that you want to execute
 * @2 => the action that tou want to use, if the app only contains
 *       one action, this parameter is not necesary
 * @3 => the id used in some actions, for example, to get the data
 *       of specific customer using the id
 */

// Check for rest/1, that is the name of the app to load
set_data('rest/1', encode_bad_chars(strval(get_data('rest/1'))));
if (get_data('rest/1') == '') {
    show_json_error('App not found');
}

$file = detect_app_file(get_data('rest/1'));
if (!file_exists($file)) {
    $app = get_data('rest/1');
    show_json_error("App $app not found");
}

// Load the app xml file
$array = xmlfile2array($file);
if (!is_array($array) || !count($array)) {
    show_php_error(['phperror' => 'Internal error']);
}

$found = false;

// Check for rest/2, that is the name of the action to load
set_data('rest/2', encode_bad_chars(strval(get_data('rest/2'))));
if (get_data('rest/2') == '' && count($array) == 1) {
    set_data('rest/2', key($array));
    $found = true;
}

if (!$found && get_data('rest/2') == '') {
    $items = [];
    foreach ($array as $key => $val) {
        $candidate = is_attr_value($val) && isset($val['#attr']['default']);
        if ($candidate && eval_bool($val['#attr']['default'])) {
            $items[] = $key;
        }
    }
    if (count($items) > 1) {
        show_php_error(['phperror' => 'Multiple default nodes found']);
    }
    if (count($items) == 1) {
        $key = $items[0];
        set_data('rest/2', $key);
        $found = true;
    }
}

if (get_data('rest/2') == '') {
    show_json_error('Action not found');
}

// Trick to allow requests like widget/table2 that is <widget id="table2">
if (!$found && get_data('rest/3') != '') {
    $items = [];
    foreach ($array as $key => $val) {
        if (fix_key($key) == get_data('rest/2')) {
            $candidate = is_attr_value($val) && isset($val['#attr']['id']);
            if ($candidate && $val['#attr']['id'] == get_data('rest/3')) {
                $items[] = $key;
            }
        }
    }
    if (count($items) > 1) {
        $action = get_data('rest/2');
        $id = get_data('rest/3');
        show_php_error(['phperror' => "Multiple repeated nodes <$action id='$id'> found"]);
    }
    if (count($items) == 1) {
        $key = $items[0];
        set_data('rest/2', $key);
        $found = true;
    }
}

// Trick to detect list when not is the first list element
if (!$found) {
    $items = [];
    foreach ($array as $key => $val) {
        $candidate = is_attr_value($val) && isset($val['#attr']['id']);
        if (!$candidate && fix_key($key) == get_data('rest/2')) {
            $items[] = $key;
        }
    }
    if (count($items) > 1) {
        $action = get_data('rest/2');
        show_php_error(['phperror' => "Multiple repeated nodes <$action> found"]);
    }
    if (count($items) == 1) {
        $key = $items[0];
        set_data('rest/2', $key);
        $found = true;
    }
}

// Trick to allow request like <create id="insert"> using only insert
// Only valid here if id="insert" only appear one time, repetitions are ignored
if (!$found) {
    $items = [];
    foreach ($array as $key => $val) {
        $candidate = is_attr_value($val) && isset($val['#attr']['id']);
        if ($candidate && $val['#attr']['id'] == get_data('rest/2')) {
            $items[] = $key;
        }
    }
    if (count($items) == 1) {
        $key = $items[0];
        $val = $array[$key];
        $rest = get_data('rest');
        array_splice($rest, 2, 1, [$key, $val['#attr']['id']]);
        set_data('rest', $rest);
        $found = true;
    }
}

if (!isset($array[get_data('rest/2')])) {
    $action = get_data('rest/2');
    show_json_error("Action $action not found");
}

// Get only the action part
$array = $array[get_data('rest/2')];
set_data('rest/2', fix_key(get_data('rest/2')));

// Check if array contains an eval attr
$candidate = is_attr_value($array) && isset($array['#attr']['eval']);
if ($candidate && eval_bool($array['#attr']['eval'])) {
    $array = eval("return {$array['value']};");
}

// Clean some old attributes
if (is_attr_value($array)) {
    foreach (['default', 'id'] as $attr) {
        if (isset($array['#attr'][$attr])) {
            unset($array['#attr'][$attr]);
        }
    }
}

// This line is a trick to allow attr in the action
$array = join_attr_value($array);
if (!is_array($array) || !count($array)) {
    show_php_error(['phperror' => 'Internal error']);
}

// Connect to the database
db_connect();

// Eval the check/app/queries
$first = true;
foreach ($array as $key => $val) {
    // Control that the first node is a check node
    if ($first) {
        if (fix_key($key) != 'check') {
            show_json_error('Permission denied');
        }
        $first = false;
    }
    // Evaluate the node
    $val = eval_attr($val);
    if (fix_key($key) == 'check') {
        // If the node is a check, can contains a message
        $message = 'Permission denied';
        $logout = false;
        if (is_attr_value($val)) {
            if (isset($val['#attr']['message'])) {
                $message = $val['#attr']['message'];
            }
            if (isset($val['#attr']['logout'])) {
                $logout = eval_bool($val['#attr']['logout']);
            }
            $val = $val['value'];
        }
        // And now, we must check the returned value
        if (!$val) {
            show_json_error($message, $logout);
        }
        // As note: all checks are removed from the array
        unset($array[$key]);
        continue;
    } elseif (fix_key($key) == 'temp') {
        // All temp nodes are removed
        unset($array[$key]);
        continue;
    } else {
        // This check allow to process the case ifeval="false"
        // In this case, the returned value is null
        if ($val === null) {
            unset($array[$key]);
            continue;
        } else {
            $array[$key] = $val;
        }
    }
    // Search for output nodes
    if (fix_key($key) == 'output') {
        $array = $val;
        break;
    }
}

// The end
output_handler_json($array);
