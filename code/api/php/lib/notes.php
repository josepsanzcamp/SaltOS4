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
 * Notes module
 *
 * This file provide some usefull functions for the notes module
 */

/**
 * Check Notes Old
 *
 * This function returns true or false and is an utility to know if the ui
 * must to shown the needed widgets related with the old notes
 *
 * @app    => app that you want to use
 * @action => action that you want to do (create, view, edit)
 * @id     => register of the app that must contain notes
 */
function check_notes_old($app, $action, $id = null)
{
    // Check for action
    if (!in_array($action, ['view', 'edit'], true)) {
        return false;
    }
    // Check the app table
    $table = app2table($app);
    if ($table === '') {
        return false;
    }
    // Check if notes table exists
    $query = "SELECT id FROM {$table}_notes LIMIT 1";
    if (!db_check($query)) {
        return false;
    }
    // This check fix a security issue when this function is called with all
    // parameters and id is null, in this scope two parameters must be true
    if (func_num_args() === 2) {
        return true;
    }
    // Check for registers
    $query = "SELECT COUNT(*) FROM {$table}_notes WHERE reg_id = ?";
    $count = execute_query($query, [$id]);
    return boolval($count);
}

/**
 * Check Notes New
 *
 * This function returns true or false and is an utility to know if the ui
 * must to shown the needed widgets related with the new notes
 *
 * @app    => app that you want to use
 * @action => action that you want to do (create, view, edit)
 */
function check_notes_new($app, $action)
{
    // Check for action
    if (!in_array($action, ['create', 'edit'], true)) {
        return false;
    }
    // Check the app table
    $table = app2table($app);
    if ($table === '') {
        return false;
    }
    // Check if notes table exists
    $query = "SELECT id FROM {$table}_notes LIMIT 1";
    if (!db_check($query)) {
        return false;
    }
    // All is successfully
    return true;
}
