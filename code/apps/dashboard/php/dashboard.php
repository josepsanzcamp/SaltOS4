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
 * Dashboard and navbar generation logic.
 *
 * This file contains helper functions used to build the SaltOS dashboard and navbar
 * dynamically based on the applications configured in the system and the permissions
 * of the current user.
 *
 * Functions included:
 * - __dashboard_helper(): Generates the full dashboard widget layout (alerts, buttons, separators)
 * - __dashboard_config(): Applies user-specific configuration to customize the dashboard layout
 * - __navbar_helper(): Builds the application menu structure for the top navigation bar
 *
 * These functions are used internally by the SaltOS UI rendering engine to generate
 * the initial layout and navigation menus seen by each user on login.
 */

/**
 * Build the default dashboard layout with groups and application buttons.
 *
 * This function retrieves all active applications from `tbl_apps`,
 * groups them by `group`, filters them by user permissions (menu access),
 * and then formats them into a widget-based structure suitable for rendering
 * on the dashboard, including alerts, buttons, and separators.
 *
 * Each group of apps is preceded by an alert (group title and description),
 * followed by buttons for each app, and a horizontal rule (`<hr>`) at the end.
 *
 * @return array An array of widgets (alerts, buttons, hr) to render the dashboard
 */
function __dashboard_helper()
{
    // Create the groups apps list
    $query = 'SELECT code, `group`, name, description, color, opacity
        FROM tbl_apps WHERE active = 1 ORDER BY position DESC,name ASC';
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
    $query = 'SELECT code, name, description, color
        FROM tbl_apps_groups ORDER BY position DESC, name ASC';
    $rows = execute_query_array($query);
    $mapping = array_combine(array_column($rows, 'code'), $rows);

    // Sort the groups using the mapping order
    $temp = [];
    foreach ($mapping as $key => $val) {
        if (isset($groups[$key])) {
            $temp[$key] = $groups[$key];
        }
    }
    $groups = $temp;

    // Create the lineal apps list
    $items = [];
    foreach ($groups as $group => $rows) {
        if (!isset($mapping[$group])) {
            show_php_error(['phperror' => "group $group not found"]);
        }
        // Add the alert
        $xml = '<alert id="group/{$code}" title="{$name}" text="{$description}"
            col_class="col-12 mb-3" color="{$color}"/>';
        $xml = str_replace_assoc([
            '{$code}' => $mapping[$group]['code'],
            '{$name}' => T($mapping[$group]['name'], $rows[0]['code']),
            '{$description}' =>
                str_replace('&', '&amp;', T($mapping[$group]['description'], $rows[0]['code'])),
            '{$color}' => $mapping[$group]['color'],
        ], $xml);
        $array = xml2array($xml);
        set_array($items, 'alert', $array['alert']);
        foreach ($rows as $row) {
            $xml = '<button id="app/{$code}" onclick="saltos.window.open(\'app/{$code}\')"
                class="w-100 h-100 fs-1 opacity-{$opacity}" label="{$name}"
                tooltip="{$description}" color="{$color}"/>';
            $xml = str_replace_assoc([
                '{$code}' => $row['code'],
                '{$name}' => T($row['name'], $row['code']),
                '{$description}' => T($row['description'], $row['code']),
                '{$color}' => $row['color'],
                '{$opacity}' => $row['opacity'],
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
 * Build a personalized dashboard layout based on user config.
 *
 * This function loads the full list of dashboard items using `__dashboard_helper()`
 * and then tries to fetch the widget configuration saved by the current user
 * under the path `app/dashboard/widgets/default`.
 *
 * If configuration is found, it reconstructs the dashboard by preserving only
 * the items selected by the user, in the specified order. Otherwise, it returns
 * the default layout.
 *
 * @return array The personalized or default dashboard widget list
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
 * Build the application menu for the navbar.
 *
 * This function generates the main application menu for the navbar by:
 * - Fetching all active apps grouped by `group`
 * - Filtering them by user permissions
 * - Mapping the group metadata from `tbl_apps_groups`
 * - Creating a linear structure of `<item>` elements including:
 *   - Group labels (disabled items)
 *   - App entries with onclick handlers
 *   - A divider after each group
 *
 * The result is an array of menu items that represents the full application
 * navigation tree grouped visually in the UI.
 *
 * @return array An array of navbar `<item>` definitions
 */
function __navbar_helper()
{
    // Create the groups apps list
    $query = 'SELECT code, `group`, name, description, color, opacity
        FROM tbl_apps WHERE active = 1 ORDER BY position DESC,name ASC';
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
    $query = 'SELECT code, name, description, color
        FROM tbl_apps_groups ORDER BY position DESC, name ASC';
    $rows = execute_query_array($query);
    $mapping = array_combine(array_column($rows, 'code'), $rows);

    // Sort the groups using the mapping order
    $temp = [];
    foreach ($mapping as $key => $val) {
        if (isset($groups[$key])) {
            $temp[$key] = $groups[$key];
        }
    }
    $groups = $temp;

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
            '{$name}' => T($mapping[$group]['name'], $rows[0]['code']),
        ], $xml);
        $array = xml2array($xml);
        set_array($items, 'item', $array['item']);
        // Add all items of the group
        foreach ($rows as $row) {
            $xml = '<item label="{$name}" onclick="saltos.window.open(\'app/{$code}\')"/>';
            $xml = str_replace_assoc([
                '{$code}' => $row['code'],
                '{$name}' => T($row['name'], $row['code']),
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
