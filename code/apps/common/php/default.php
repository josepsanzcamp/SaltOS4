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
 * This file contains functions intended to be used as hepers of other functions,
 * allowing to convert between formats like yaml to xml using a small specification
 */

/**
 * Make app file
 *
 * This function returns the xml of an app using the follow specs passed in @data:
 *
 * @require   => this field defines the required php that contains the make_app_file
 *               function like this file
 * @_template => the xml app file used as template, this file contains the default
 *               specification of the app used in the quick apps defined in the yaml
 * @screen    => the screen used by the template
 * @list      => the list of fields used in the list screen, this must be a list of
 *               arrays with 3 elements: id, type and label, types can be as follow:
 *               -text    => default field with text
 *               -select  => this field resolves the text from a table and field, see
 *                           the @select spec
 *               -boolean => this field uses an icon with V o X in green or red to
 *                           see the value of the field (generaly 1 or 0)
 *               -hastext => this field is similar to boolean but the true or false
 *                           is detedmined using the contents of the text, no text is
 *                           false and some text is true
 * @form      => the list of fields used in the form screen, this must be a list of
 *               arrays with 3 elements: id, type and label, types can be as follow:
 *               - text, date, time, datetime, checkbox, switch => default widget
 *               - textarea, ckeditor, codemirror => widget with 10em of height
 *               - select => widget that uses the select spec to resolve the contents
 * @select    => this spec is intended to defined the selects used in the list and
 *               forms, requires an array of 3 elements: id, table and field, if the
 *               last field is not specified, saltos tries to resolve it using the
 *               manifest information (internally use the table2field feature)
 * @attr      => this part contain an array with extra features added to the forms
 *               widgets, the spec requires to use a named array that defines the
 *               id of the field and each array entry must to be another pair of
 *               key and val with the name of the property and the value of it
 */
function make_app_file($data)
{
    // load the template and apply specific features
    $array = xmlfile2array($data['template']);

    // initial checks
    $screen = &$array['main']['screen'];
    if (!isset($screen)) {
        show_php_error(['phperror' => 'main/screen node not found']);
    }
    if ($screen != 'TODO') {
        show_php_error(['phperror' => 'main/screen TODO not found']);
    }

    $dropdown = &$array['list#1']['value']['layout']['value']['row#1']['value']['table']['#attr']['dropdown'];
    if (!isset($dropdown)) {
        show_php_error(['phperror' => 'list/dropdown attr not found']);
    }
    if ($dropdown != 'TODO') {
        show_php_error(['phperror' => 'list/dropdown TODO not found']);
    }

    $header = &$array['list#1']['value']['layout']['value']['row#1']['value']['table']['value']['header'];
    if (!isset($header)) {
        show_php_error(['phperror' => 'list/header node not found']);
    }
    if ($header != 'TODO') {
        show_php_error(['phperror' => 'list/header TODO not found']);
    }

    $actions = &$array['list#1']['value']['layout']['value']['row#1']['value']['table']['value']['actions'];
    if (!isset($actions)) {
        show_php_error(['phperror' => 'list/actions node not found']);
    }
    if (!is_array($actions)) {
        show_php_error(['phperror' => 'list/actions ARRAY not found']);
    }

    $list = &$array['list#2']['value']['data']['value'];
    if (!isset($list)) {
        show_php_error(['phperror' => 'list/data node not found']);
    }
    if (!strpos($list, 'TODO')) {
        show_php_error(['phperror' => 'list/data TODO not found']);
    }

    $form = &$array['_form'];
    if (!isset($form)) {
        show_php_error(['phperror' => 'form node not found']);
    }
    if ($form != 'TODO') {
        show_php_error(['phperror' => 'form TODO not found']);
    }

    $col_class = [
        'create' => &$array['create#1']['value']['layout']['value']['row']['#attr']['col_class'],
        'view' => &$array['view#1']['value']['layout']['value']['row']['#attr']['col_class'],
        'edit' => &$array['edit#1']['value']['layout']['value']['row']['#attr']['col_class'],
    ];
    foreach ($col_class as $key => $val) {
        if (!isset($col_class[$key])) {
            show_php_error(['phperror' => "$key col_class not found"]);
        }
        if ($col_class[$key] != 'TODO') {
            show_php_error(['phperror' => "$key col_class TODO not found"]);
        }
    }

    // set the screen
    $screen = $data['screen'];

    // connect to the database if needed
    $bool = get_config('db/obj');
    if (!$bool) {
        db_connect();
    }

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
                $val[2] = table2field($val[1]);
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
    $header = xml2array($xml)['root'];

    // update the actions array
    foreach ($actions as $key => $action) {
        if (!__app_has_perm($data['app'], $key)) {
            unset($actions[$key]);
        }
    }

    // set the dropdown attr to true or false string
    if ($data['dropdown'] === null || $data['dropdown'] === 'auto') {
        $dropdown = count($actions) == 1 ? 'false' : 'true';
    } elseif ($data['dropdown'] === true) {
        $dropdown = 'true';
    } elseif ($data['dropdown'] === false) {
        $dropdown = 'false';
    } else {
        show_php_error(['phperror' => 'unknown dropdown value']);
    }

    // remove labels in actions if needed
    if ($dropdown == 'false') {
        foreach ($actions as $key => $action) {
            unset($actions[$key]['#attr']['label']);
        }
    }

    // set the select fields list
    $query = $list;
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
    $list = $query;

    // set the form fields
    $xml = [];
    foreach ($data['form'] as $field) {
        $id = $field['id'];
        $type = $field['type'];
        $label = $field['label'];
        $attr = $data['attr'][$id] ?? [];
        foreach ($attr as $key => $val) {
            $attr[$key] = "$key=\"$val\"";
        }
        $attr = implode(' ', $attr);
        switch ($type) {
            case 'text':
            case 'date':
            case 'time':
            case 'datetime':
            case 'checkbox':
            case 'switch':
            case 'integer':
            case 'float':
            case 'color':
            case 'textarea':
            case 'ckeditor':
            case 'codemirror':
                $xml[] = "<$type id=\"$id\" label=\"$label\" $attr/>";
                break;
            case 'select':
                $field2 = $data['select'][$id]['field'];
                $table2 = $data['select'][$id]['table'];
                $xml[] = "<select id=\"$id\" label=\"$label\" $attr>";
                $xml[] = "<rows eval=\"true\">execute_query_array(\"
                    SELECT '' label, '0' value UNION
                    SELECT $field2 label, id value FROM $table2\")</rows>";
                $xml[] = '</select>';
                break;
            case 'multiselect':
                $field2 = $data['select'][$id]['field'];
                $table2 = $data['select'][$id]['table'];
                $xml[] = "<multiselect id=\"$id\" label=\"$label\" $attr>";
                $xml[] = "<rows eval=\"true\">execute_query_array(\"
                    SELECT $field2 label, id value FROM $table2\")</rows>";
                $xml[] = '</multiselect>';
                break;
            default:
                show_php_error(['phperror' => "$type not found"]);
        }
    }
    $xml = '<root>' . implode('', $xml) . '</root>';
    $form = xml2array($xml)['root'];

    // set the col_class if needed
    foreach ($col_class as $key => $val) {
        $col_class[$key] = $data['col_class'];
    }

    // disconnect to the database if needed
    if (!$bool) {
        db_disconnect();
    }

    // end of function
    return $array;
}
