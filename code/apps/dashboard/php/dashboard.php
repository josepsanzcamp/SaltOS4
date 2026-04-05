<?php

/**
 *  ____        _ _    ___  ____  _  _
 * / ___|  __ _| | |_ / _ \/ ___|| || |
 * \___ \ / _` | | __| | | \___ \| || |_
 *  ___) | (_| | | |_| |_| |___) |__   _|
 * |____/ \__,_|_|\__|\___/|____/   |_|
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
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
    // Create the apps list
    $query = 'SELECT code, name, description, color, layout
        FROM tbl_apps WHERE active = 1';
    $rows = execute_query_array($query);
    $apps = [];
    foreach ($rows as $row) {
        if (check_user($row['code'], 'menu')) {
            [$row['x'], $row['y'], $row['w'], $row['h']] = explode(',', $row['layout']);
            $apps[] = $row;
        }
    }

    // Prepare the groups list
    $query = 'SELECT code, name, description, color, layout
        FROM tbl_apps_groups WHERE active = 1';
    $rows = execute_query_array($query);
    $groups = [];
    foreach ($rows as $row) {
        [$row['x'], $row['y'], $row['w'], $row['h']] = explode(',', $row['layout']);
        $groups[] = $row;
    }

    // Create the widgets list
    $query = 'SELECT code, name, description, app, layout
        FROM tbl_apps_widgets WHERE active = 1';
    $rows = execute_query_array($query);
    $widgets = [];
    foreach ($rows as $row) {
        if (check_user($row['app'], 'widget')) {
            [$row['x'], $row['y'], $row['w'], $row['h']] = explode(',', $row['layout']);
            $widgets[] = $row;
        }
    }

    // Create the lineal items list
    $items = [];

    // Add the alert
    foreach ($groups as $row) {
        $xml = '<alert id="alert_{$code}" title="{$name}" text="{$description}" class="h-100"
            color="{$color}" x="{$x}" y="{$y}" w="{$w}" h="{$h}"/>';
        $xml = str_replace_assoc([
            '{$code}' => $row['code'],
            '{$name}' => T($row['name'], $row['code']),
            '{$description}' => str_replace('&', '&amp;', T($row['description'], $row['code'])),
            '{$color}' => $row['color'],
            '{$x}' => $row['x'],
            '{$y}' => $row['y'],
            '{$w}' => $row['w'],
            '{$h}' => $row['h'],
        ], $xml);
        $array = xml2array($xml);
        set_array($items, 'alert', $array['alert']);
    }

    // Add the buttons
    foreach ($apps as $row) {
        $xml = '<button id="button_{$code}" onclick="saltos.window.open(\'app/{$code}\')"
            class="w-100 h-100" label="{$name}" tooltip="{$description}"
            color="{$color}" x="{$x}" y="{$y}" w="{$w}" h="{$h}"/>';
        $xml = str_replace_assoc([
            '{$code}' => $row['code'],
            '{$name}' => T($row['name'], $row['code']),
            '{$description}' => T($row['description'], $row['code']),
            '{$color}' => $row['color'],
            '{$x}' => $row['x'],
            '{$y}' => $row['y'],
            '{$w}' => $row['w'],
            '{$h}' => $row['h'],
        ], $xml);
        $array = xml2array($xml);
        set_array($items, 'button', $array['button']);
    }

    // Add the widgets
    foreach ($widgets as $row) {
        $xml = '<widget id="widget_{$code}" source="app/{$app}/widget/{$code}"
            x="{$x}" y="{$y}" w="{$w}" h="{$h}"/>';
        $xml = str_replace_assoc([
            '{$code}' => $row['code'],
            '{$app}' => $row['app'],
            '{$x}' => $row['x'],
            '{$y}' => $row['y'],
            '{$w}' => $row['w'],
            '{$h}' => $row['h'],
        ], $xml);
        $array = xml2array($xml);
        set_array($items, 'widget', $array['widget']);
    }

    return $items;
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
    $query = 'SELECT code, name, description, `group`
        FROM tbl_apps WHERE active = 1 ORDER BY position ASC, name ASC';
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
    $query = 'SELECT code, name, description
        FROM tbl_apps_groups WHERE active = 1 ORDER BY position ASC, name ASC';
    $rows = execute_query_array($query);
    $mapping = array_combine(array_column($rows, 'code'), $rows);
    $mapping = array_intersect_key($mapping, $groups);

    // Create the lineal apps list
    $items = [];
    foreach ($mapping as $group => $ginfo) {
        // Add the group name using an item disabled
        $xml = '<item label="{$name}" disabled="true"/>';
        $xml = str_replace_assoc([
            '{$code}' => $ginfo['code'],
            '{$name}' => T($ginfo['name'], $groups[$group][0]['code']),
        ], $xml);
        $array = xml2array($xml);
        set_array($items, 'item', $array['item']);

        // Add all items of the group
        foreach ($groups[$group] as $row) {
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
