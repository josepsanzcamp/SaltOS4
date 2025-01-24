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
function check_files_old($app, $action, $id)
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
    // Check if files table exists
    $query = "SELECT id FROM {$table}_files LIMIT 1";
    if (!db_check($query)) {
        return false;
    }
    // Check for registers
    $query = "SELECT COUNT(*) FROM {$table}_files WHERE reg_id = ?";
    $count = execute_query($query, [$id]);
    if (!$count) {
        return false;
    }
    // All is successfully
    return true;
}

/**
 * TODO
 *
 * TODO
 */
function check_files_new($app, $action)
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
    // Check if files table exists
    $query = "SELECT id FROM {$table}_files LIMIT 1";
    if (!db_check($query)) {
        return false;
    }
    // All is successfully
    return true;
}

/**
 * Get cid
 *
 * This function returns the requested attachment indentified by the cid argument
 *
 * @id  => id of the email
 * @cid => the cid of the content requested
 */
function files_cid($app, $id, $cid)
{
    // Check the app table
    $table = app2table($app);
    if ($table == '') {
        show_php_error(['phperror' => 'table not found']);
    }
    // check that app folder exists
    $files = get_directory('dirs/filesdir') ?? getcwd_protected() . '/data/files/';
    if (!file_exists($files . $app)) {
        show_json_error('file not found');
    }
    // Prepare the output
    $result = execute_query("SELECT * FROM {$table}_files WHERE id = ? AND reg_id = ?", [$cid, $id]);
    if (!is_array($result)) {
        //~ show_php_error(['phperror' => 'file not found']);
        show_json_error('file not found');
    }
    return [
        'name' => $result['name'],
        'size' => $result['size'],
        'type' => $result['type'],
        'file' => $result['file'],
        'dir' => $files . $app,
    ];
}

/**
 * Get viewpdf
 *
 * This function returns the requested attachment indentified by the cid argument
 * in a pdf format for the viewpdf widget
 *
 * @id  => id of the email
 * @cid => the cid of the content requested
 */
function files_viewpdf($app, $id, $cid)
{
    $file = files_cid($app, $id, $cid);
    $ext = strtolower(extension($file['name']));
    if (!$ext) {
        $ext = strtolower(saltos_content_type1($file['type']));
    }
    $cache1 = get_cache_file([$app, $id, $cid], $ext);
    copy($file['dir'] . '/' . $file['file'], $cache1);
    chmod_protected($cache1, 0666);
    // CREAR THUMBS SI ES NECESARIO
    $cache2 = get_cache_file([$app, $id, $cid], 'pdf');
    if (!file_exists($cache2)) {
        require_once 'php/lib/unoconv.php';
        file_put_contents($cache2, unoconv2pdf($cache1));
        if (!filesize($cache2)) {
            require_once 'php/lib/pdf.php';
            file_put_contents($cache2, __pdf_all2pdf($cache1));
        }
        chmod_protected($cache2, 0666);
    }
    if (!file_exists($cache2)) {
        show_php_error(['phperror' => 'File not found']);
    }
    return base64_encode(file_get_contents($cache2));
}

/**
 * Get download
 *
 * This function returns the requested attachment indentified by the cid argument
 * in an array format for the download feature
 *
 * @id  => id of the email
 * @cid => the cid of the content requested
 */
function files_download($app, $id, $cid)
{
    $file = files_cid($app, $id, $cid);
    $file['data'] = base64_encode(file_get_contents($file['dir'] . '/' . $file['file']));
    return $file;
}
