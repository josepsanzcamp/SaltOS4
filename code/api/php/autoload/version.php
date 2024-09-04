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
 * Version helper module
 *
 * This fie contains useful functions related to the version of the SaltOS of the php engine
 */

/**
 * Get Name Version Revision
 *
 * This function returns a string with the SaltOS name, version, revision and
 * copyright if needed
 *
 * @copyright => boolean to specify if you want to add the copyright to the output
 */
function get_name_version_revision($copyright = false)
{
    $NAME = 'SaltOS';
    $VERSION = '4.0';
    $REVISION = svnversion();
    $COPYRIGHT = 'Copyright (C) 2007-2024 by Josep Sanz Campderrós';
    $result = "$NAME v$VERSION r$REVISION";
    if ($copyright) {
        $result .= ", $COPYRIGHT";
    }
    return $result;
}

/**
 * SVN Version
 *
 * This function tries to return the svn version of the project
 *
 * @dir => allow to specify where do you want to execute the svnversion command
 */
function svnversion($dir = null)
{
    if ($dir === null) {
        $dir = getcwd_protected();
    }
    $version = __svnversion_helper($dir);
    if (!$version) {
        $file = get_server('SCRIPT_FILENAME');
        if (is_link($file)) {
            $dir = dirname(readlink($file));
            $version = __svnversion_helper($dir);
        }
    }
    return $version;
}

/**
 * SVN Version helper
 *
 * This function tries to return the svn version of the project
 *
 * @dir => allow to specify where do you want to execute the svnversion command
 */
function __svnversion_helper($dir)
{
    // Using regular file
    if (file_exists("{$dir}/svnversion")) {
        return intval(file_get_contents("{$dir}/svnversion"));
    }
    // Using svnversion
    if (
        check_commands(
            get_config('commands/svnversion') ?? 'svnversion',
            get_config('commands/commandexpires') ?? 60
        )
    ) {
        return intval(ob_passthru(str_replace(
            ['__DIR__'],
            [$dir],
            get_config('commands/__svnversion__') ?? 'cd __DIR__; svnversion'
        ), get_config('commands/commandexpires') ?? 60));
    }
    // Nothing to do
    return 0;
}

/**
 * GIT Version
 *
 * This function tries to return the git version of the project
 *
 * @dir => allow to specify where do you want to execute the gitversion command
 */
function gitversion($dir = null)
{
    if ($dir === null) {
        $dir = getcwd_protected();
    }
    $version = __gitversion_helper($dir);
    if (!$version) {
        $file = get_server('SCRIPT_FILENAME');
        if (is_link($file)) {
            $dir = dirname(readlink($file));
            $version = __gitversion_helper($dir);
        }
    }
    return $version;
}

/**
 * GIT Version helper
 *
 * This function tries to return the git version of the project
 *
 * @dir => allow to specify where do you want to execute the gitversion command
 */
function __gitversion_helper($dir)
{
    // Using regular file
    if (file_exists("{$dir}/gitversion")) {
        return intval(file_get_contents("{$dir}/gitversion"));
    }
    // Using git
    if (
        check_commands(
            get_config('commands/gitversion') ?? 'git',
            get_config('commands/commandexpires') ?? 60
        )
    ) {
        return intval(ob_passthru(str_replace(
            ['__DIR__'],
            [$dir],
            get_config('commands/__gitversion__') ?? 'cd __DIR__; git rev-list HEAD --count'
        ), get_config('commands/commandexpires') ?? 60));
    }
    // Nothing to do
    return 0;
}

/**
 * IS PHP
 *
 * This function returns a boolean as a response about the comparison between the
 * version requested and the current version.
 *
 * @version => the version where do you want to compare
 */
function isphp($version)
{
    return version_compare(PHP_VERSION, strval($version), '>=');
}
