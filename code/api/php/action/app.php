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
 * @2 => the subapp that tou want to use, if the app only contains
 *       one subapp, this parameter is not necesary
 * @3 => the id used in some subapps, for example, to get the data
 *       of specific customer using the id
 *
 * List action (triggered when type attr is table or list)
 *
 * This action tries to facility the creation of lists with the tipicals
 * features suck as rows, actions for each row, and other improvements as
 * the list with count and without count.
 *
 * TODO: pending to add the order by from the list header
 */

// Check for rest/1, that is the name of the app to load
set_data('rest/1', encode_bad_chars(strval(get_data('rest/1'))));
if (get_data('rest/1') == '') {
    show_json_error('app not found');
}

$file = 'apps/' . get_data('rest/1') . '/xml/' . get_data('rest/1') . '.xml';
if (!file_exists($file)) {
    $files = glob('apps/*/xml/' . get_data('rest/1') . '.xml');
    if (count($files) == 1) {
        $file = $files[0];
    }
}
if (!file_exists($file)) {
    show_json_error('app ' . get_data('rest/1') . ' not found');
}

// Load the app xml file
$array = xmlfile2array($file);
if (!is_array($array) || !count($array)) {
    show_json_error('internal error');
}

// Check for rest/2, that is the name of the subapp to load
set_data('rest/2', encode_bad_chars(strval(get_data('rest/2'))));
if (get_data('rest/2') == '' && count($array) == 1) {
    set_data('rest/2', key($array));
}

if (get_data('rest/2') == '') {
    foreach ($array as $key => $val) {
        if (is_attr_value($val) && isset($val['#attr']['default']) && eval_bool($val['#attr']['default'])) {
            set_data('rest/2', $key);
            break;
        }
    }
}

if (get_data('rest/2') == '') {
    show_json_error('subapp not found');
}

if (!isset($array[get_data('rest/2')])) {
    // Trick to allow request like <create id="insert"> using only insert
    foreach ($array as $key => $val) {
        if (is_attr_value($val) && isset($val['#attr']['id']) && $val['#attr']['id'] == get_data('rest/2')) {
            $rest = get_data('rest');
            array_splice($rest, 2, 1, [$key, $val['#attr']['id']]);
            set_data('rest', $rest);
            break;
        }
    }
} elseif (get_data('rest/3')) {
    // Trick to allow requests like widget/table2 that is <widget id="table2">
    foreach ($array as $key => $val) {
        if (fix_key($key) == get_data('rest/2')) {
            if (
                is_attr_value($val) && isset($val['#attr']['id']) && $val['#attr']['id'] == get_data('rest/3')
            ) {
                set_data('rest/2', $key);
                break;
            }
        }
    }
}

if (!isset($array[get_data('rest/2')])) {
    show_json_error('subapp ' . get_data('rest/2') . ' not found');
}

// Get only the subapp part
$array = $array[get_data('rest/2')];
set_data('rest/2', fix_key(get_data('rest/2')));

// Check if array contains an eval attr
if (is_attr_value($array) && isset($array['#attr']['eval']) && eval_bool($array['#attr']['eval'])) {
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

// This line is a trick to allow attr in the subapp
$array = join_attr_value($array);

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
