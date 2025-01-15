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
    $data['fields'] = array_combine(array_column($data['fields'], 0), $data['fields']);
    if (isset($data['select'])) {
        $data['select'] = array_combine(array_column($data['select'], 0), $data['select']);
        foreach ($data['select'] as $key => $val) {
            if (!isset($val[2])) {
                db_connect();
                $data['select'][$key][2] = table2field($val[1]);
                db_disconnect();
            }
        }
    }
    // set the list header
    $xml = [];
    foreach ($data['list'] as $field) {
        $label = $data['fields'][$field][2];
        $type = $data['fields'][$field][1];
        switch ($type) {
            case 'text':
            case 'textarea':
            case 'ckeditor':
            case 'codemirror':
            case 'select':
            case 'date':
            case 'time':
            case 'datetime':
                $xml[] = "<$field label=\"$label\"/>";
                break;
            case 'boolean':
                $xml[] = "<$field label=\"$label\" type=\"icon\"/>";
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
        $type = $data['fields'][$field][1];
        $field_fixed = escape_reserved_word($field);
        switch ($type) {
            case 'text':
            case 'textarea':
            case 'ckeditor':
            case 'codemirror':
            case 'date':
            case 'time':
            case 'datetime':
                $fields[] = $field_fixed;
                break;
            case 'boolean':
                $fields[] = "CASE $field_fixed
                    WHEN 1 THEN 'check-lg text-success' ELSE 'x-lg text-danger' END $field_fixed";
                break;
            case 'select':
                $field2 = $data['select'][$field][2];
                $table2 = $data['select'][$field][1];
                $fields[] = "(SELECT $field2 FROM $table2 WHERE $table2.id = $field_fixed) $field_fixed";
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
        $label = $data['fields'][$field][2];
        $type = $data['fields'][$field][1];
        switch ($type) {
            case 'text':
            case 'date':
            case 'time':
            case 'datetime':
                $xml[] = "<$type id=\"$field\" label=\"$label\"/>";
                break;
            case 'textarea':
            case 'ckeditor':
            case 'codemirror':
                $xml[] = "<$type id=\"$field\" label=\"$label\" height=\"10em\"/>";
                break;
            case 'boolean':
                $xml[] = "<switch id=\"$field\" label=\"$label\"/>";
                break;
            case 'select':
                $field2 = $data['select'][$field][2];
                $table2 = $data['select'][$field][1];
                $xml[] = "<select id=\"$field\" label=\"$label\">";
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
