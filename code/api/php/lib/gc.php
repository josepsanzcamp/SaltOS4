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
        if ($dir === '') {
            show_php_error(['phperror' => 'Internal error']);
        }
        $files1 = glob_protected($dir . '*'); // Visible files
        $files2 = glob_protected($dir . '.*'); // Hidden files
        $exceptions = [$dir . '.', $dir . '..', $dir . '.htaccess', $dir . '.gitignore']; // Exceptions
        $files = array_merge($files1, array_diff($files2, $exceptions));
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
