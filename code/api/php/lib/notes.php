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
function check_notes_old($app, $action, $id = null)
{
    // Check for action
    if (!in_array($action, ['view', 'edit'])) {
        return false;
    }
    // Check the app table
    $table = app2table($app);
    if ($table == '') {
        return false;
    }
    // Check if notes table exists
    $query = "SELECT id FROM {$table}_notes LIMIT 1";
    if (!db_check($query)) {
        return false;
    }
    // This check fix a security issue when this function is called with all
    // parameters and id is null, in this scope two parameters must be true
    if (func_num_args() == 2) {
        return true;
    }
    // Check for registers
    $query = "SELECT COUNT(*) FROM {$table}_notes WHERE reg_id = ?";
    $count = execute_query($query, [$id]);
    return boolval($count);
}

/**
 * TODO
 *
 * TODO
 */
function check_notes_new($app, $action)
{
    // Check for action
    if (!in_array($action, ['create', 'edit'])) {
        return false;
    }
    // Check the app table
    $table = app2table($app);
    if ($table == '') {
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
