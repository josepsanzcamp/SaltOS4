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
    $views = ['list', 'form'];
    foreach ($views as $view) {
        foreach ($data[$view] as $key => $val) {
            $data[$view][$key] = array_combine($keys, $val);
        }
    }
    // convert the select array into a key => val array using id as key
    if (isset($data['select'])) {
        $keys = ['id', 'table', 'field'];
        foreach ($data['select'] as $key => $val) {
            // as an additional feature, tries to resolve the field if there is not found
            if (!isset($val[2])) {
                $bool = get_config('db/obj');
                if (!$bool) {
                    db_connect();
                }
                $val[2] = table2field($val[1]);
                if (!$bool) {
                    db_disconnect();
                }
            }
            $data['select'][$key] = array_combine($keys, $val);
        }
        $data['select'] = array_combine(array_column($data['select'], 'id'), $data['select']);
    }
    // set the list header
    $xml = [];
    foreach ($data['list'] as $field) {
        $id = $field['id'];
        $type = $field['type'];
        $label = $field['label'];
        switch ($type) {
            case 'text':
            case 'select':
                $xml[] = "<$id label=\"$label\"/>";
                break;
            case 'boolean':
            case 'hastext':
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
        $id = $field['id'];
        $type = $field['type'];
        $label = $field['label'];
        $fixed = escape_reserved_word($id);
        switch ($type) {
            case 'text':
                $fields[] = $fixed;
                break;
            case 'boolean':
                $fields[] = "CASE $fixed
                    WHEN 1 THEN 'check-lg text-success' ELSE 'x-lg text-danger' END $fixed";
                break;
            case 'hastext':
                $fields[] = "CASE $fixed
                    WHEN '' THEN 'x-lg text-danger' ELSE 'check-lg text-success' END $fixed";
                break;
            case 'select':
                $field2 = $data['select'][$id]['field'];
                $table2 = $data['select'][$id]['table'];
                $fields[] = "IFNULL((SELECT $field2 FROM $table2 WHERE $table2.id = $fixed), '') $fixed";
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
        $id = $field['id'];
        $type = $field['type'];
        $label = $field['label'];
        $extra = $data['extra'][$id] ?? [];
        foreach ($extra as $key => $val) {
            $extra[$key] = "$key=\"$val\"";
        }
        $extra = implode(' ', $extra);
        switch ($type) {
            case 'text':
            case 'date':
            case 'time':
            case 'datetime':
            case 'checkbox':
            case 'switch':
                $xml[] = "<$type id=\"$id\" label=\"$label\" $extra/>";
                break;
            case 'textarea':
            case 'ckeditor':
            case 'codemirror':
                $xml[] = "<$type id=\"$id\" label=\"$label\" height=\"10em\" $extra/>";
                break;
            case 'select':
                $field2 = $data['select'][$id]['field'];
                $table2 = $data['select'][$id]['table'];
                $xml[] = "<select id=\"$id\" label=\"$label\" $extra>";
                $xml[] = "<rows eval=\"true\">execute_query_array(\"SELECT '' label, '0' value
                    UNION SELECT $field2 label, id value FROM $table2\")</rows>";
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
    $xml .= "<!-- source: {$data['source']} -->";
    if ($data['indent'] ?? false) {
        $xml .= "\n\n";
    }
    $xml .= array2xml(['root' => $array], $data['indent'] ?? false);
    return $xml;
}
