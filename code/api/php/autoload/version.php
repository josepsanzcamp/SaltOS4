<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
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
 * This function returns a string with the SaltOS name, version and revision
 */
function get_name_version_revision()
{
    return __name_version_revision('SaltOS', '4.1', svnversion());
}

/**
 * Helper Name Version Revision
 *
 * This function returns a string of the form 'NAME vVERSION rREVISION'
 *
 * @name     => The string used as name
 * @version  => The string used as version
 * @revision => The string used as revision
 *
 * Notes:
 *
 * This function only tries to formalize the about string used in SaltOS
 */
function __name_version_revision($name, $version, $revision)
{
    return "$name v$version r$revision";
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
    if (file_exists("$dir/svnversion")) {
        return intval(file_get_contents("$dir/svnversion"));
    }
    // Using svnversion
    if (check_commands('svnversion')) {
        $expires = get_config('server/commandexpires') ?? 60;
        return intval(ob_passthru("cd $dir; svnversion 2>/dev/null", $expires));
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
    if (file_exists("$dir/gitversion")) {
        return intval(file_get_contents("$dir/gitversion"));
    }
    // Using git
    if (check_commands('git')) {
        $expires = get_config('server/commandexpires') ?? 60;
        return intval(ob_passthru("cd $dir; git rev-list HEAD --count 2>/dev/null", $expires));
    }
    // Nothing to do
    return 0;
}
