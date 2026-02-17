<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
 */

declare(strict_types=1);

/**
 * Data Actions Merger
 *
 * This file contains functionality for merging action controls
 * with data rows in tables or lists, including permission checks
 */

/**
 * Merge data with actions
 *
 * Combines dataset rows with action controls while verifying permissions.
 * Processes each action definition, checks user permissions, and attaches
 * permitted actions to each data row.
 *
 * @data    => Dataset containing rows to display in table/list
 * @actions => Action definitions to merge with each data row
 *
 * Returns the modified dataset with actions attached to each row
 *
 * Throws error if actions parameter is not an array
 */
function __merge_data_actions($data, $actions)
{
    // Validate and prepare action definitions
    if (!is_array($actions)) {
        show_php_error(['phperror' => 'actions must be an array']);
    }

    // Process each action definition
    foreach ($actions as $key => $action) {
        // Normalize action attributes
        $action = join_attr_value($action);
        $action = eval_attr($action);

        // Extract base action name and check permissions
        $action0 = get_part_from_string($action['action'], '/', 0);
        if (__app_has_perm($action['app'], $action0)) {
            $actions[$key] = $action;
        } else {
            // Remove action if user lacks permission
            unset($actions[$key]);
        }
    }

    // Attach permitted actions to each data row
    foreach ($data as $key => $row) {
        $merge = [];
        foreach ($actions as $key2 => $action) {
            // Build action argument URL
            $action['arg'] = "app/{$action["app"]}/{$action["action"]}/{$row["id"]}";

            // Remove app/action metadata from final output
            unset($action['app']);
            unset($action['action']);

            $merge[$key2] = $action;
        }

        // Only add actions array if there are permitted actions
        if (count($merge)) {
            $data[$key]['actions'] = $merge;
        }
    }

    return $data;
}
