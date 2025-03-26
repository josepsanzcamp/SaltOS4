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
 * Insert action
 *
 * This action allow to insert registers in the database associated to
 * each app
 *
 * TODO
 */
function insert($app, $data)
{
    require_once 'php/lib/control.php';
    require_once 'php/lib/log.php';
    require_once 'php/lib/version.php';
    require_once 'php/lib/indexing.php';
    require_once 'php/lib/upload.php';

    if (!is_array($data) || !count($data)) {
        return [
            'status' => 'ko',
            'text' => 'Data not found',
            'code' => __get_code_from_trace(),
        ];
    }

    $table = app2table($app);
    $fields = array_flip(array_column(get_fields($table), 'name'));
    $subtables = array_flip(array_diff(array_column(app2subtables($app), 'alias'), ['']));
    $files = app2files($app) ? array_flip(['addfiles']) : [];
    $notes = app2notes($app) ? array_flip(['addnotes']) : [];
    $error = array_diff_key($data, $fields, $subtables, $files, $notes);
    if (count($error)) {
        return [
            'status' => 'ko',
            'text' => 'Fields mismatch: ' . implode(', ', array_keys($error)),
            'code' => __get_code_from_trace(),
        ];
    }

    // Separate the data associated to a subtables
    $subdata = array_intersect_key($data, $subtables);
    $filesdata = array_intersect_key($data, $files);
    $notesdata = array_intersect_key($data, $notes);
    $data = array_diff_key($data, $subdata, $files, $notes);

    // Prepare main query
    $query = prepare_insert_query($table, $data);
    db_query(...$query);

    $id = db_last_insert_id();

    // Prepare all subqueries
    $subtables = app2subtables($app);
    foreach ($subtables as $temp) {
        $alias = $temp['alias'];
        $subtable = $temp['subtable'];
        $field = $temp['field'];
        $fields = array_flip(array_column(get_fields($subtable), 'name'));
        if (isset($subdata[$alias])) {
            foreach ($subdata[$alias] as $temp2) {
                $error = array_diff_key($temp2, $fields);
                if (count($error)) {
                    return [
                        'status' => 'ko',
                        'text' => 'Fields mismatch: ' . implode(', ', array_keys($error)),
                        'code' => __get_code_from_trace(),
                    ];
                }
                $temp2[$field] = $id;
                $query = prepare_insert_query($subtable, $temp2);
                db_query(...$query);
            }
        }
    }

    // Prepare notes
    if (app2notes($app) && isset($notesdata['addnotes'])) {
        $temp = [
            'user_id' => current_user(),
            'datetime' => current_datetime(),
            'reg_id' => $id,
            'note' => $notesdata['addnotes'],
        ];
        $query = prepare_insert_query("{$table}_notes", $temp);
        db_query(...$query);
    }

    // Prepare files
    if (app2files($app) && isset($filesdata['addfiles'])) {
        foreach ($filesdata['addfiles'] as $file) {
            if (
                check_upload_file([
                    'user_id' => current_user(),
                    'uniqid' => $file['id'],
                    'app' => $file['app'],
                    'name' => $file['name'],
                    'size' => $file['size'],
                    'type' => $file['type'],
                    'file' => $file['file'],
                    'hash' => $file['hash'],
                ])
            ) {
                rename_upload_file($file, $app, $id);
            }
        }
    }

    make_control($app, $id);
    make_log($app, 'insert', $id);
    make_version($app, $id);
    make_index($app, $id);

    return [
        'status' => 'ok',
        'created_id' => $id,
    ];
}

/**
 * Update action
 *
 * This action allow to update registers in the database associated to
 * each app and requires the app, id, data and a valid token.
 *
 * TODO
 */
function update($app, $id, $data)
{
    require_once 'php/lib/control.php';
    require_once 'php/lib/log.php';
    require_once 'php/lib/version.php';
    require_once 'php/lib/indexing.php';
    require_once 'php/lib/upload.php';
    require_once 'php/lib/trash.php';

    if (!is_array($data) || !count($data)) {
        return [
            'status' => 'ko',
            'text' => 'Data not found',
            'code' => __get_code_from_trace(),
        ];
    }

    $table = app2table($app);
    $fields = array_flip(array_column(get_fields($table), 'name'));
    $subtables = array_flip(array_diff(array_column(app2subtables($app), 'alias'), ['']));
    $files = app2files($app) ? array_flip(['addfiles', 'delfiles']) : [];
    $notes = app2notes($app) ? array_flip(['addnotes', 'delnotes']) : [];
    $error = array_diff_key($data, $fields, $subtables, $files, $notes);
    if (count($error)) {
        return [
            'status' => 'ko',
            'text' => 'Fields mismatch: ' . implode(', ', array_keys($error)),
            'code' => __get_code_from_trace(),
        ];
    }

    // Separate the data associated to a subtables
    $subdata = array_intersect_key($data, $subtables);
    $filesdata = array_intersect_key($data, $files);
    $notesdata = array_intersect_key($data, $notes);
    $data = array_diff_key($data, $subdata, $files, $notes);

    // Prepare main query
    if (count($data)) {
        $query = prepare_update_query($table, $data, [
            'id' => $id,
        ]);
        db_query(...$query);
    }

    // Prepare all subqueries
    $subtables = app2subtables($app);
    foreach ($subtables as $temp) {
        $alias = $temp['alias'];
        $subtable = $temp['subtable'];
        $field = $temp['field'];
        $fields = array_flip(array_column(get_fields($subtable), 'name'));
        if (isset($subdata[$alias])) {
            foreach ($subdata[$alias] as $temp2) {
                $error = array_diff_key($temp2, $fields);
                if (count($error)) {
                    return [
                        'status' => 'ko',
                        'text' => 'Fields mismatch: ' . implode(', ', array_keys($error)),
                        'code' => __get_code_from_trace(),
                    ];
                }
                if (!isset($temp2['id'])) {
                    // Insert new subdata
                    $temp2[$field] = $id;
                    $query = prepare_insert_query($subtable, $temp2);
                    db_query(...$query);
                } elseif (intval($temp2['id']) > 0) {
                    // Update the subdata
                    $id2 = intval($temp2['id']);
                    unset($temp2['id']);
                    $query = prepare_update_query($subtable, $temp2, [
                        'id' => $id2,
                        $field => $id,
                    ]);
                    db_query(...$query);
                } elseif (intval($temp2['id']) < 0) {
                    // Delete the subdata
                    $id2 = -intval($temp2['id']);
                    $query = "DELETE FROM $subtable WHERE id = ? AND $field = ?";
                    db_query($query, [$id2, $id]);
                } else {
                    show_php_error(['phperror' => 'subdata found with id=0']);
                }
            }
        }
    }

    // Remove selected notes
    if (app2notes($app) && isset($notesdata['delnotes'])) {
        $ids = check_ids_array($notesdata['delnotes']);
        foreach ($ids as $id2) {
            $query = "DELETE FROM {$table}_notes WHERE reg_id = ? AND id = ?";
            db_query($query, [$id, $id2]);
        }
    }

    // Prepare notes
    if (app2notes($app) && isset($notesdata['addnotes'])) {
        $temp = [
            'user_id' => current_user(),
            'datetime' => current_datetime(),
            'reg_id' => $id,
            'note' => $notesdata['addnotes'],
        ];
        $query = prepare_insert_query("{$table}_notes", $temp);
        db_query(...$query);
    }

    // Remove selected files
    if (app2files($app) && isset($filesdata['delfiles'])) {
        $ids = check_ids_array($filesdata['delfiles']);
        foreach ($ids as $id2) {
            send_trash_file($app, $id, $id2);
        }
    }

    // Prepare files
    if (app2files($app) && isset($filesdata['addfiles'])) {
        foreach ($filesdata['addfiles'] as $file) {
            if (
                check_upload_file([
                    'user_id' => current_user(),
                    'uniqid' => $file['id'],
                    'app' => $file['app'],
                    'name' => $file['name'],
                    'size' => $file['size'],
                    'type' => $file['type'],
                    'file' => $file['file'],
                    'hash' => $file['hash'],
                ])
            ) {
                rename_upload_file($file, $app, $id);
            }
        }
    }

    make_control($app, $id);
    make_log($app, 'update', $id);
    make_version($app, $id);
    make_index($app, $id);

    return [
        'status' => 'ok',
        'updated_id' => $id,
    ];
}

/**
 * Delete action
 *
 * This action allow to delete registers in the database associated to
 * each app
 *
 * TODO
 */
function delete($app, $id)
{
    require_once 'php/lib/control.php';
    require_once 'php/lib/log.php';
    require_once 'php/lib/version.php';
    require_once 'php/lib/indexing.php';
    require_once 'php/lib/depend.php';
    require_once 'php/lib/trash.php';

    $depend = check_dependencies($app, $id);
    // Remove this app in the dependencies array
    foreach ($depend as $key => $val) {
        if (isset($val['app']) && $val['app'] == $app) {
            unset($depend[$key]);
        }
    }
    // If dependencies are found, break the execution
    if (count($depend)) {
        $apps = [];
        $others = [];
        foreach ($depend as $key => $val) {
            if (isset($val['app'])) {
                $apps[$val['app']] = $val['app'];
            } else {
                $others[$val['table']] = $val['table'];
            }
        }
        $message = [];
        if (count($apps)) {
            foreach ($apps as $key => $val) {
                $apps[$key] = T($val);
            }
            $message[] = implode(', ', $apps);
        }
        $others = count($others);
        if ($others) {
            if ($others == 1) {
                $message[] = $others . ' ' . T('internal table');
            } else {
                $message[] = $others . ' ' . T('internal tables');
            }
        }
        $message = implode(', ', $message);
        return [
            'status' => 'ko',
            'text' => T('Data used by others apps') . ': ' . $message,
            'code' => __get_code_from_trace(),
        ];
    }

    // Prepare main query
    $table = app2table($app);
    $query = "DELETE FROM $table WHERE id = ?";
    db_query($query, [$id]);

    // Prepare all subqueries
    $subtables = app2subtables($app);
    foreach ($subtables as $temp) {
        $subtable = $temp['subtable'];
        $field = $temp['field'];
        $query = "DELETE FROM $subtable WHERE $field = ?";
        db_query($query, [$id]);
    }

    // Remove all notes
    if (app2notes($app)) {
        $query = "DELETE FROM {$table}_notes WHERE reg_id = ?";
        db_query($query, [$id]);
    }

    // Remove all files
    if (app2files($app)) {
        $query = "SELECT id FROM {$table}_files WHERE reg_id = ?";
        $ids = execute_query_array($query, [$id]);
        foreach ($ids as $id2) {
            send_trash_file($app, $id, $id2);
        }
    }

    make_control($app, $id);
    make_log($app, 'delete', $id);
    make_version($app, $id);
    make_index($app, $id);

    return [
        'status' => 'ok',
        'deleted_id' => $id,
    ];
}
