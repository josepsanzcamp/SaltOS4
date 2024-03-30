<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2024 by Josep Sanz Campderrós
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
 * System helper module
 *
 * This fie contains useful functions related to system checks, allow to detect dependencies not
 * installed on the system, or misconfigurations on the SaltOS installation
 */

/**
 * Check System
 *
 * This function checks the system to detect if all knowed dependencies are found in the system, to do it,
 * defines an array with the type (class or function), the name and some extra info for the error message
 * that is triggered if the dependency is not satisfied
 *
 * Too, check all directories of the data directory to validate that the process can write inside it
 */
function check_system()
{
    // PACKAGE CHECKS
    $array = [
        ["class_exists", "DomElement", "Class", "php-xml"],
        ["function_exists", "imagecreatetruecolor", "Function", "php-gd"],
        ["function_exists", "mb_check_encoding", "Function", "php-mbstring"],
    ];
    foreach ($array as $a) {
        if (!$a[0]($a[1])) {
            // @codeCoverageIgnoreStart
            show_php_error([
                "phperror" => "$a[2] $a[1] not found",
                "details" => "Try to install $a[3] package",
            ]);
            // @codeCoverageIgnoreEnd
        }
    }
    // DIRECTORIES CKECKS
    $dirs = glob("data/*");
    foreach ($dirs as $dir) {
        if (!file_exists($dir) || !is_dir($dir) || !is_writable($dir)) {
            show_php_error([
                "phperror" => "$dir not writable",
                "details" => "Try to set permissions to do writable the $dir directory",
            ]);
        }
    }
}
