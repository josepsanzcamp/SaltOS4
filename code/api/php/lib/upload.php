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
 * Add file
 *
 * This function is intended to add files to the upload mecano
 *
 * @val  => is an associative array with the follow entries:
 * @id   => the uniq id used to identify the file in the uploads system
 * @name => local name of the file
 * @size => size of the contents of the file
 * @type => mime type of the file
 * @data => the inline data in base64 form, with the mime type prefix
 */
function add_file($val)
{
    // Get data and remove it from files[key]
    $data = $val['data'];
    $val['data'] = '';
    // Check for the data prefix
    $pre = "data:{$val["type"]};base64,";
    $len = strlen($pre);
    if (strncmp($pre, $data, $len) != 0) {
        return $val;
    }
    // Check for the data size
    $data = base64_decode(substr($data, $len));
    if (strlen($data) != $val['size']) {
        return $val;
    }
    // Store it in a local file
    $val['file'] = time() . '_' . get_unique_id_md5() . '_' . encode_bad_chars_file($val['name']);
    $dir = get_directory('dirs/uploaddir') ?? getcwd_protected() . '/data/upload/';
    file_put_contents($dir . $val['file'], $data);
    chmod_protected($dir . $val['file'], 0666);
    // Compute the hash
    $val['hash'] = md5($data);
    // Do the insert
    $user_id = current_user();
    $datetime = current_datetime();
    $query = prepare_insert_query('tbl_uploads', [
        'user_id' => $user_id,
        'datetime' => $datetime,
        'uniqid' => $val['id'],
        'app' => $val['app'],
        'name' => $val['name'],
        'size' => $val['size'],
        'type' => $val['type'],
        'file' => $val['file'],
        'hash' => $val['hash'],
    ]);
    db_query(...$query);
    return $val;
}

/**
 * Del file
 *
 * This function is intended to del files in the upload mecano
 *
 * @val  => is an associative array with the follow entries:
 * @id   => the uniq id used to identify the file in the uploads system
 * @name => local name of the file
 * @size => size of the contents of the file
 * @type => mime type of the file
 * @file => the inline data in base64 form, with the mime type prefix
 * @hash => the hash of the binary data contents
 */
function del_file($val)
{
    $user_id = current_user();
    // Check integrity with the database entry
    $id = check_file([
        'user_id' => $user_id,
        'uniqid' => $val['id'],
        'app' => $val['app'],
        'name' => $val['name'],
        'size' => $val['size'],
        'type' => $val['type'],
        'file' => $val['file'],
        'hash' => $val['hash'],
    ]);
    if (!$id) {
        return $val;
    }
    // Check for file name integrity
    if (encode_bad_chars_file($val['file']) != $val['file']) {
        return $val;
    }
    // Check for the file exists and is a file
    $dir = get_directory('dirs/uploaddir') ?? getcwd_protected() . '/data/upload/';
    if (!file_exists($dir . $val['file']) || !is_file($dir . $val['file'])) {
        return $val;
    }
    // Check for file size integrity
    if (filesize($dir . $val['file']) != $val['size']) {
        return $val;
    }
    // Check for file hash integrity
    if (md5_file($dir . $val['file']) != $val['hash']) {
        return $val;
    }
    // Remove the local file
    unlink($dir . $val['file']);
    // Remove the database entry
    $query = 'DELETE FROM tbl_uploads WHERE id = ?';
    db_query($query, [$id]);
    // Reset vars
    $val['file'] = '';
    $val['hash'] = '';
    return $val;
}

/**
 * Check file
 *
 * This function is intended to check files in the upload mecano
 *
 * @val     => is an associative array with the follow entries:
 * @user_id => the user id intended to identify the property of the file
 * @uniqid  => the uniq id used to identify the file in the uploads system
 * @name    => local name of the file
 * @size    => size of the contents of the file
 * @type    => mime type of the file
 * @file    => the inline data in base64 form, with the mime type prefix
 * @hash    => the hash of the binary data contents
 */
function check_file($val)
{
    [$where, $values] = prepare_where_query('tbl_uploads', $val);
    $query = 'SELECT id FROM tbl_uploads WHERE ' . $where;
    $id = execute_query($query, $values);
    return $id;
}

/**
 * Copy file
 *
 * This function copy the file contained in the val parameter to the
 * app folder and create the files register using the id argument
 *
 * @val => the array with the file data
 * @app => the app code that identify the application
 * @id  => the id of the application register
 */
function copy_file($val, $app, $id)
{
    // check that app folder exists
    $files = get_directory('dirs/filesdir') ?? getcwd_protected() . '/data/files/';
    if (!file_exists($files . $app)) {
        mkdir($files . $app);
        chmod_protected($files . $app, 0777);
    }
    // Copy the file to the app folder
    $upload = get_directory('dirs/uploaddir') ?? getcwd_protected() . '/data/upload/';
    copy($upload . $val['file'], $files . $app . '/' . $val['file']);
    chmod_protected($files . $app . '/' . $val['file'], 0666);
    // Create the app register
    $user_id = current_user();
    $datetime = current_datetime();
    $table = app2table($app);
    $query = prepare_insert_query("{$table}_files", [
        'user_id' => $user_id,
        'datetime' => $datetime,
        'reg_id' => $id,
        'uniqid' => $val['id'],
        'name' => $val['name'],
        'size' => $val['size'],
        'type' => $val['type'],
        'file' => $val['file'],
        'hash' => $val['hash'],
    ]);
    db_query(...$query);
}

/**
 * Garbage Collector Upload
 *
 * This function tries to clean the upload database of old files, the parameters
 * that this function uses is defined in the config file, only uses the timeout
 * that is getted from the server/cachetimeout
 */
function gc_upload()
{
    $delta = current_datetime(-intval(get_config('server/cachetimeout')));
    $query = 'SELECT id, file FROM tbl_uploads WHERE datetime < ?';
    $files = execute_query_array($query, [$delta]);
    $dir = get_directory('dirs/uploaddir') ?? getcwd_protected() . '/data/upload/';
    $output = [];
    foreach ($files as $file) {
        if (file_exists($dir . $file['file']) && is_file($dir . $file['file'])) {
            unlink($dir . $file['file']);
        }
        $query = 'DELETE FROM tbl_uploads WHERE id = ?';
        db_query($query, [$file['id']]);
        $output[] = $dir . $file['file'];
    }
    return $output;
}
