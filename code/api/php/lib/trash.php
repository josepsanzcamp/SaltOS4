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
 * Garbage Collector Trash
 *
 * This function tries to clean the trash database of old files, the parameters
 * that this function uses is defined in the config file, only uses the timeout
 * that is getted from the server/trashtimeout
 */
function gc_trash()
{
    $delta = current_datetime(-intval(get_config('server/trashtimeout')));
    $query = 'SELECT id, file FROM tbl_trash WHERE datetime < ?';
    $files = execute_query_array($query, [$delta]);
    $dir = get_directory('dirs/trashdir') ?? getcwd_protected() . '/data/trash/';
    $output = [];
    foreach ($files as $file) {
        if (file_exists($dir . $file['file']) && is_file($dir . $file['file'])) {
            unlink($dir . $file['file']);
        }
        $query = 'DELETE FROM tbl_trash WHERE id = ?';
        db_query($query, [$file['id']]);
        $output[] = $dir . $file['file'];
    }
    return $output;
}
