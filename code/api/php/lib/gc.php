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
 * Garbage collector helper module
 *
 * This fie contains useful functions related to the garbaging unused resources, currently only
 * implements the clear of temporary files
 */

/**
 * Garbage Collector Executor
 *
 * This function tries to clean the directories of old files, the parameters
 * that this function uses are defined in the config file, the timeout is
 * getted from the server/cachetimeout config file key, too is able to detect
 * hidden files and remove except the special files as current directory,
 * parent directory and htaccess file
 */
function gc_exec()
{
    $dirs = [
        get_directory('dirs/cachedir') ?? getcwd_protected() . '/data/cache/'
            => get_config('server/cachetimeout'),
        get_directory('dirs/tempdir') ?? getcwd_protected() . '/data/temp/'
            => get_config('server/cachetimeout'),
        get_directory('dirs/uploaddir') ?? getcwd_protected() . '/data/upload/'
            => get_config('server/cachetimeout'),
        get_directory('dirs/trashdir') ?? getcwd_protected() . '/data/trash/'
            => get_config('server/trashtimeout'),
    ];
    $output = [];
    foreach ($dirs as $dir => $timeout) {
        if ($dir == '') {
            show_php_error(['phperror' => 'Internal error']);
        }
        $files1 = glob_protected($dir . '*'); // Visible files
        $files2 = glob_protected($dir . '.*'); // Hidden files
        $files2 = array_diff($files2, [$dir . '.', $dir . '..', $dir . '.htaccess']); // Exceptions
        $files = array_merge($files1, $files2);
        $delta = time() - intval($timeout);
        foreach ($files as $file) {
            if (file_exists($file) && is_file($file) && filemtime($file) < $delta) {
                unlink($file);
                $output[] = $file;
            }
        }
    }
    return $output;
}
