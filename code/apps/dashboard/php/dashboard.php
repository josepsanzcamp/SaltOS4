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
 * TODO
 *
 * TODO
 */

/**
 * TODO
 *
 * TODO
 */
function __dashboard_helper()
{
    // Create the groups apps list
    $query = 'SELECT code, `group`, name, description
        FROM tbl_apps WHERE active = 1 ORDER BY `group`,name ASC';
    $rows = execute_query_array($query);
    $groups = [];
    foreach ($rows as $row) {
        if (!check_user($row['code'], 'menu')) {
            continue;
        }
        $group = $row['group'];
        if (!isset($groups[$group])) {
            $groups[$group] = [];
        }
        $groups[$group][] = $row;
    }

    // Prepare the mapping
    $query = 'SELECT code, name, description FROM tbl_apps_groups';
    $rows = execute_query_array($query);
    $mapping = array_combine(array_column($rows, 'code'), $rows);

    // Create the lineal apps list
    $items = [];
    foreach ($groups as $group => $rows) {
        if (!isset($mapping[$group])) {
            show_php_error(['phperror' => "group $group not found"]);
        }
        // Add the alert
        $xml = '<alert id="group/{$code}" title="{$name}" text="{$description}" col_class="col-12 mb-3"/>';
        $xml = str_replace_assoc([
            '{$code}' => $mapping[$group]['code'],
            '{$name}' => $mapping[$group]['name'],
            '{$description}' => str_replace('&', '&amp;', $mapping[$group]['description']),
        ], $xml);
        $array = xml2array($xml);
        set_array($items, 'alert', $array['alert']);
        foreach ($rows as $row) {
            $xml = '<button id="app/{$code}" onclick="saltos.window.open(\'app/{$code}\')"
                class="fs-1 w-100 h-100" label="{$name}" tooltip="{$description}"/>';
            $xml = str_replace_assoc([
                '{$code}' => $row['code'],
                '{$name}' => $row['name'],
                '{$description}' => $row['description'],
            ], $xml);
            $array = xml2array($xml);
            set_array($items, 'button', $array['button']);
        }
        // Add the hr
        $xml = '<hr id="separator" col_class="col-12 clonable" class="mt-0 border-3"/>';
        $array = xml2array($xml);
        set_array($items, 'hr', $array['hr']);
    }
    array_pop($items);
    return $items;
}

/**
 * TODO
 *
 * TODO
 */
function __dashboard_config()
{
    $items = __dashboard_helper();
    $mapping = [];
    foreach ($items as $key => $val) {
        $mapping[$val['#attr']['id']] = $key;
    }

    $config = get_config_array('app/dashboard/widgets/default', current_user());
    if (isset($config['app/dashboard/widgets/default'])) {
        $config = json_decode($config['app/dashboard/widgets/default'], true);
    }
    if (!is_array($config) || !count($config)) {
        return $items;
    }

    $result = [];
    foreach ($config as $item) {
        $key = $mapping[$item];
        $val = $items[$key];
        set_array($result, $key, $val);
    }
    return $result;
}

/**
 * TODO
 *
 * TODO
 */
function __navbar_helper()
{
    // Create the groups apps list
    $query = 'SELECT code, `group`, name, description
        FROM tbl_apps WHERE active = 1 ORDER BY `group`,name ASC';
    $rows = execute_query_array($query);
    $groups = [];
    foreach ($rows as $row) {
        if (!check_user($row['code'], 'menu')) {
            continue;
        }
        $group = $row['group'];
        if (!isset($groups[$group])) {
            $groups[$group] = [];
        }
        $groups[$group][] = $row;
    }

    // Prepare the mapping
    $query = 'SELECT code, name, description FROM tbl_apps_groups';
    $rows = execute_query_array($query);
    $mapping = array_combine(array_column($rows, 'code'), $rows);

    // Create the lineal apps list
    $items = [];
    foreach ($groups as $group => $rows) {
        if (!isset($mapping[$group])) {
            show_php_error(['phperror' => "group $group not found"]);
        }
        // Add the group name using an item disabled
        $xml = '<item label="{$name}" disabled="true"/>';
        $xml = str_replace_assoc([
            '{$code}' => $mapping[$group]['code'],
            '{$name}' => $mapping[$group]['name'],
        ], $xml);
        $array = xml2array($xml);
        set_array($items, 'item', $array['item']);
        // Add all items of the group
        foreach ($rows as $row) {
            $xml = '<item label="{$name}" onclick="saltos.window.open(\'app/{$code}\')"/>';
            $xml = str_replace_assoc([
                '{$code}' => $row['code'],
                '{$name}' => $row['name'],
            ], $xml);
            $array = xml2array($xml);
            set_array($items, 'item', $array['item']);
        }
        // Add the divider at the end
        set_array($items, 'item', xml2array('<item divider="true"/>')['item']);
    }
    array_pop($items);
    return $items;
}
