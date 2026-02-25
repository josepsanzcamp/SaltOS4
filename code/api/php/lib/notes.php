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
    if (!in_array($action, ['view', 'edit'])) {
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
    if (!in_array($action, ['create', 'edit'])) {
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

/**
 * Check Notes
 *
 * Unified check for the subtable notes control, returns true if the app
 * supports notes and the action is one of create, view or edit.
 *
 * @app    => app that you want to use
 * @action => action that you want to do (create, view, edit)
 */
function check_notes($app, $action)
{
    if (!in_array($action, ['create', 'view', 'edit'])) {
        return false;
    }
    $table = app2table($app);
    if ($table === '') {
        return false;
    }
    $query = "SELECT id FROM {$table}_notes LIMIT 1";
    if (!db_check($query)) {
        return false;
    }
    return true;
}

/**
 * Notes Decode
 *
 * Decode the allnotes field from a JSON string (sent by the subtable control)
 * into the addnotes, delnotes and updatenotes fields that actions.php expects.
 * Each row in the flat array follows the actions.php id convention:
 * - No id => new note (addnotes)
 * - Positive id => edited note (updatenotes)
 * - Negative id => deleted note (delnotes)
 *
 * @json => the form data array
 *
 * Return the data array with allnotes replaced by addnotes/delnotes/updatenotes
 */
function notes_decode(array $json): array
{
    if (!isset($json['allnotes'])) {
        return $json;
    }

    $rows = $json['allnotes'];
    if (is_string($rows)) {
        $rows = json_decode($rows, true);
    }
    unset($json['allnotes']);

    if (!is_array($rows)) {
        return $json;
    }

    $addnotes = [];
    $updatenotes = [];
    $delnotes = [];

    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        if (!isset($row['id'])) {
            if (isset($row['note']) && strval($row['note']) !== '') {
                $addnotes[] = strval($row['note']);
            }
        } elseif (intval($row['id']) > 0) {
            if (isset($row['note'])) {
                $updatenotes[] = [
                    'id' => intval($row['id']),
                    'note' => strval($row['note']),
                ];
            }
        } elseif (intval($row['id']) < 0) {
            $delnotes[] = -intval($row['id']);
        }
    }

    if (count($addnotes)) {
        $json['addnotes'] = $addnotes;
    }
    if (count($updatenotes)) {
        $json['updatenotes'] = $updatenotes;
    }
    if (count($delnotes)) {
        $json['delnotes'] = implode(',', $delnotes);
    }

    return $json;
}