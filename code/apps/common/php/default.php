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
 * Apps helper module
 *
 * This file contains functions intended to be used as hepers of other functions, allowing to convert
 * between formats as the name of the app to and app id, or viceversa
 */

/**
 * TODO
 *
 * TODO
 */
function make_app_file($data)
{
    // load the template and apply specific features
    $array = xmlfile2array($data['template']);
    // set the screen
    $array['screen'] = $data['screen'];
    // convert the fields array into a key => val array using the id as key
    $keys = ['id', 'type', 'label'];
    foreach ($data['fields'] as $key => $val) {
        foreach ($keys as $key2 => $val2) {
            if (isset($val[$key2])) {
                $data['fields'][$key][$val2] = $val[$key2];
                unset($data['fields'][$key][$key2]);
            }
        }
    }
    $data['fields'] = array_combine(array_column($data['fields'], 'id'), $data['fields']);
    // convert the select array into a key => val array using id as key
    if (isset($data['select'])) {
        $keys = ['id', 'table', 'field'];
        foreach ($data['select'] as $key => $val) {
            foreach ($keys as $key2 => $val2) {
                if (isset($val[$key2])) {
                    $data['select'][$key][$val2] = $val[$key2];
                    unset($data['select'][$key][$key2]);
                }
            }
        }
        $data['select'] = array_combine(array_column($data['select'], 'id'), $data['select']);
        // as an additional feature, tries to resolve the field if there is not found
        foreach ($data['select'] as $key => $val) {
            if (!isset($val['field'])) {
                $bool = get_config('db/obj');
                if (!$bool) {
                    db_connect();
                }
                $data['select'][$key]['field'] = table2field($val['table']);
                if (!$bool) {
                    db_disconnect();
                }
            }
        }
    }
    // set the list header
    $xml = [];
    foreach ($data['list'] as $field) {
        $id = $data['fields'][$field]['id'];
        $type = $data['fields'][$field]['type'];
        $label = $data['fields'][$field]['label'];
        switch ($type) {
            case 'text':
            case 'textarea':
            case 'ckeditor':
            case 'codemirror':
            case 'select':
            case 'date':
            case 'time':
            case 'datetime':
                $xml[] = "<$id label=\"$label\"/>";
                break;
            case 'boolean':
                $xml[] = "<$id label=\"$label\" type=\"icon\"/>";
                break;
            default:
                show_php_error(['phperror' => "$type not found"]);
        }
    }
    $xml = '<root>' . implode('', $xml) . '</root>';
    $array['list#1']['value']['header'] = xml2array($xml)['root'];
    // set the select fields list
    $query = $array['list#1']['value']['data']['value'];
    $fields = [];
    foreach ($data['list'] as $field) {
        $id = $data['fields'][$field]['id'];
        $type = $data['fields'][$field]['type'];
        $label = $data['fields'][$field]['label'];
        $fixed = escape_reserved_word($id);
        switch ($type) {
            case 'text':
            case 'textarea':
            case 'ckeditor':
            case 'codemirror':
            case 'date':
            case 'time':
            case 'datetime':
                $fields[] = $fixed;
                break;
            case 'boolean':
                $fields[] = "CASE $fixed
                    WHEN 1 THEN 'check-lg text-success' ELSE 'x-lg text-danger' END $fixed";
                break;
            case 'select':
                $field2 = $data['select'][$field]['field'];
                $table2 = $data['select'][$field]['table'];
                $fields[] = "(SELECT $field2 FROM $table2 WHERE $table2.id = $fixed) $fixed";
                break;
            default:
                show_php_error(['phperror' => "$type not found"]);
        }
    }
    $fields = implode(', ', $fields);
    $query = str_replace('TODO', $fields, $query);
    $array['list#1']['value']['data']['value'] = $query;
    // set the form fields
    $xml = [];
    foreach ($data['form'] as $field) {
        $id = $data['fields'][$field]['id'];
        $type = $data['fields'][$field]['type'];
        $label = $data['fields'][$field]['label'];
        $extras = $data[$field] ?? [];
        foreach ($extras as $key => $val) {
            $extras[$key] = "$key=\"$val\"";
        }
        $extras = implode(' ', $extras);
        switch ($type) {
            case 'text':
            case 'date':
            case 'time':
            case 'datetime':
                $xml[] = "<$type id=\"$id\" label=\"$label\" $extras/>";
                break;
            case 'textarea':
            case 'ckeditor':
            case 'codemirror':
                $xml[] = "<$type id=\"$id\" label=\"$label\" height=\"10em\" $extras/>";
                break;
            case 'boolean':
                $xml[] = "<switch id=\"$id\" label=\"$label\" $extras/>";
                break;
            case 'select':
                $field2 = $data['select'][$field]['field'];
                $table2 = $data['select'][$field]['table'];
                $xml[] = "<select id=\"$id\" label=\"$label\" $extras>";
                $xml[] = "<rows eval=\"true\">execute_query_array(\"SELECT $field2 label, id value
                    FROM $table2\")</rows>";
                $xml[] = '</select>';
                break;
            default:
                show_php_error(['phperror' => "$type not found"]);
        }
    }
    $xml = '<root>' . implode('', $xml) . '</root>';
    $array['form'] = xml2array($xml)['root'];
    // generate the output xml
    $header = file_get_contents($data['template']);
    $header = explode("\n\n", $header);
    if (substr($header[0], 0, 5) != '<?xml') {
        show_php_error(['phperror' => 'Internal error']);
    }
    if (substr($header[1], 0, 4) != '<!--') {
        show_php_error(['phperror' => 'Internal error']);
    }
    if (substr($header[2], 0, 6) != '<root>') {
        show_php_error(['phperror' => 'Internal error']);
    }
    // generate the output xml
    require_once 'php/lib/array2xml.php';
    $xml = $header[0];
    if ($data['indent'] ?? false) {
        $xml .= "\n\n";
        $xml .= $header[1];
        $xml .= "\n\n";
    }
    $xml .= array2xml(['root' => $array], $data['indent'] ?? false);
    return $xml;
}
