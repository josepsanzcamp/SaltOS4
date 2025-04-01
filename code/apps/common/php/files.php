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
 * File management functions
 *
 * These functions handle file operations such as listing files, retrieving file paths,
 * checking for file existence, and viewing file contents. They are primarily designed
 * to interact with the `data/logs` directory.
 */

/**
 * List files in the logs directory
 *
 * This function retrieves a list of files from the `data/logs` directory, applies search filters,
 * and paginates the results based on the specified offset and limit. The returned data includes
 * metadata such as file size and type.
 *
 * @search => Search term or query to filter files.
 * @offset => Offset for pagination.
 * @limit  => Maximum number of files to retrieve.
 *
 * Returns a list of files with metadata including ID, name, size, and type.
 */
function __files_list($search, $offset, $limit)
{
    $list = glob('data/logs/*'); // Retrieve all files from the logs directory

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

    // Apply offset and limit
    if ($limit !== INF) {
        $list = array_slice($list, $offset, $limit);
    }

    // Format the output with metadata
    foreach ($list as $key => $val) {
        $list[$key] = [
            'id' => basename($val),
            'name' => basename($val),
            'size' => get_human_size(filesize($val), ' ', 'bytes'),
            'type' => saltos_content_type($val),
        ];
    }

    return $list;
}

/**
 * Get the full path of a file
 *
 * This function searches for a file by its name in the `data/logs` directory and returns
 * its full path if found. Otherwise, it returns an empty string.
 *
 * @file => Name of the file to search for.
 *
 * Returns the full path of the file or an empty string if not found.
 */
function __files_getfile($file)
{
    $list = glob('data/logs/*'); // Retrieve all files from the logs directory
    foreach ($list as $key => $val) {
        if (basename($val) == $file) {
            return $val; // Return the full path if the file is found
        }
    }
    return ''; // Return an empty string if not found
}

/**
 * Check if a file exists
 *
 * This function checks whether a file exists in the `data/logs` directory by searching for its name.
 *
 * @file => Name of the file to check.
 *
 * Returns `true` if the file exists, otherwise `false`.
 */
function __files_check($file)
{
    if (__files_getfile($file)) {
        return true;
    }
    return false;
}

/**
 * View the contents of a file
 *
 * This function retrieves the contents of a file from the `data/logs` directory. It supports both
 * regular files and gzip-compressed files. The returned data includes the file name, size, type, and content.
 *
 * @file => Name of the file to view.
 *
 * Returns metadata and content of the file. If the file is not found, it returns an error response.
 */
function __files_view($file)
{
    $file = __files_getfile($file); // Get the full path of the file
    if ($file == '') {
        return [
            'status' => 'ko',
            'text' => 'File not found',
            'code' => __get_code_from_trace(),
        ]; // Return an error if the file is not found
    }

    // Retrieve file contents based on its type (regular or gzip-compressed)
    $buffer = '';
    if (extension($file) == 'gz') {
        $handle = gzopen($file, 'rb');
        $data = gzread($handle, 1024 * 1024 * 10); // Read up to 10 MB of data
        gzclose($handle);
    } else {
        $handle = fopen($file, 'rb');
        $data = fread($handle, 1024 * 1024 * 10); // Read up to 10 MB of data
        fclose($handle);
    }

    // Return the file's metadata and content
    return [
        'name' => basename($file),
        'size' => get_human_size(filesize($file), ' ', 'bytes'),
        'type' => saltos_content_type($file),
        'data' => $data,
    ];
}
