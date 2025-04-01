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
 * Certificate Management Functions
 *
 * This file contains the main functions for managing certificates, including listing,
 * inserting, checking, viewing, and deleting certificates from the NSS database.
 */

/**
 * List Certificates
 *
 * This function retrieves a list of certificates from the NSS database, applies search filters,
 * and paginates the results based on offset and limit parameters.
 *
 * @search => Search term or query to filter certificates.
 * @offset => Offset for pagination.
 * @limit  => Maximum number of certificates to retrieve.
 *
 * Return the list of certificates with their ID and name.
 */
function __certs_list($search, $offset, $limit)
{
    require_once 'apps/certs/php/nssdb.php';
    $list = __nssdb_list();

    // Apply search filters
    $search = explode_with_quotes(' ', $search);
    foreach ($search as $key => $val) {
        $val = get_string_from_quotes($val);
        $type = '+';
        while (isset($val[0]) && in_array($val[0], ['+', '-'])) {
            $type = $val[0];
            $val = substr($val, 1);
        }
        $val = get_string_from_quotes($val);
        if (!strlen($val)) {
            continue;
        }
        $list = array_grep($list, $val, $type == '-');
    }

    // Apply pagination
    if ($limit !== INF) {
        $list = array_slice($list, $offset, $limit);
    }

    // Format the list to include ID and name
    foreach ($list as $key => $val) {
        $list[$key] = ['id' => md5($val), 'name' => $val];
    }

    return $list;
}

/**
 * Insert Certificates
 *
 * This function inserts certificates into the NSS database. It validates the uploaded files,
 * processes them, and handles errors if the import is unsuccessful.
 *
 * @json => JSON object containing the certificate files and their details.
 *
 * Return the status and message of the operation.
 */
function __certs_insert($json)
{
    require_once 'php/lib/upload.php';
    require_once 'apps/certs/php/nssdb.php';
    __nssdb_init();

    $upload = get_directory('dirs/uploaddir') ?? getcwd_protected() . '/data/upload/';
    $certs = 0;
    foreach ($json['certfile'] as $file) {
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
            $output = __nssdb_add($upload . $file['file'], $json['passfile']);
            if (implode('', $output) == 'pk12util: PKCS12 IMPORT SUCCESSFUL') {
                $certs++;
            }
        }
    }
    $count = count($json['certfile']);
    if ($count != $certs) {
        return [
            'status' => 'ko',
            'text' => 'Error importing certificates',
            'code' => __get_code_from_trace(),
        ];
    }
    foreach ($json['certfile'] as $file) {
        del_upload_file($file);
    }
    return [
        'status' => 'ok',
    ];
}

/**
 * Convert Certificate Hash to Nickname
 *
 * This function maps a certificate hash to its corresponding nickname in the NSS database.
 *
 * @hash => MD5 hash of the certificate nickname.
 *
 * Return the nickname of the certificate, or an empty string if not found.
 */
function __certs_hash2nick($hash)
{
    require_once 'apps/certs/php/nssdb.php';
    $list = __nssdb_list();
    foreach ($list as $key => $val) {
        if ($hash == md5($val)) {
            return $val;
        }
    }
    return '';
}

/**
 * Check Certificate Existence
 *
 * This function checks if a certificate with the given hash exists in the NSS database.
 *
 * @hash => MD5 hash of the certificate nickname.
 *
 * Return true if the certificate exists, false otherwise.
 */
function __certs_check($hash)
{
    if (__certs_hash2nick($hash)) {
        return true;
    }
    return false;
}

/**
 * View Certificate Information
 *
 * This function retrieves detailed information about a certificate using its hash.
 *
 * @hash => MD5 hash of the certificate nickname.
 *
 * Return the status, nickname, and detailed information of the certificate.
 */
function __certs_view($hash)
{
    $nick = __certs_hash2nick($hash);
    if ($nick == '') {
        return [
            'status' => 'ko',
            'text' => 'Nick not found',
            'code' => __get_code_from_trace(),
        ];
    }
    $info = __nssdb_info($nick);
    $info['subject'] = array_map(fn($k, $v) => "$k: $v", array_keys($info['subject']), $info['subject']);
    $info['info'] = array_map(fn($k, $v) => "$k: $v", array_keys($info['info']), $info['info']);
    $info = array_merge(['[subject]'], $info['subject'], ['', '[info]'], $info['info']);
    $info = implode("\n", $info);
    return [
        'name' => $nick,
        'info' => $info,
    ];
}

/**
 * Delete Certificate
 *
 * This function removes a certificate from the NSS database using its hash.
 *
 * @hash => MD5 hash of the certificate nickname.
 *
 * Return the status of the delete operation.
 */
function __certs_delete($hash)
{
    $nick = __certs_hash2nick($hash);
    if ($nick == '') {
        return [
            'status' => 'ko',
            'text' => 'Nick not found',
            'code' => __get_code_from_trace(),
        ];
    }
    $info = __nssdb_remove($nick);
    return [
        'status' => 'ok',
    ];
}
