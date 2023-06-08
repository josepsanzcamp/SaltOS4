<?php

/*
 ____        _ _    ___  ____    _  _    ___
/ ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
\___ \ / _` | | __| | | \___ \  | || |_| | | |
 ___) | (_| | | |_| |_| |___) | |__   _| |_| |
|____/ \__,_|_|\__|\___/|____/     |_|(_)___/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2023 by Josep Sanz Campderrós
More information in https://www.saltos.org or info@saltos.org

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

declare(strict_types=1);

define("__INFO_NAME__", "SaltOS");
define("__INFO_VERSION__", "4.0");
define("__INFO_REVISION__", svnversion());
define("__INFO_COPYRIGHT__", "Copyright (C) 2007-2023 by Josep Sanz Campderrós");

/*
 * Get Name Version Revision
 *
 * This function returns a string with the SaltOS name, version, revision and
 * copyright if needed
 *
 * @copyright => boolean to specify if you want to add the copyright to the output
 */
function get_name_version_revision($copyright = false)
{
    $result = __INFO_NAME__ . " v" . __INFO_VERSION__ . " r" . __INFO_REVISION__;
    if ($copyright) {
        $result .= ", " . __INFO_COPYRIGHT__;
    }
    return $result;
}

/*
 * SVN Version
 *
 * This function tries to return the svn version of the project
 *
 * @dir => allow to specify where do you want to execute the svnversion command
 */
function svnversion($dir = ".")
{
    // USING REGULAR FILE
    if (file_exists("{$dir}/svnversion")) {
        return intval(file_get_contents("{$dir}/svnversion"));
    }
    // USING SVNVERSION
    if (check_commands(get_default("commands/svnversion", "svnversion"), get_default("default/commandexpires", 60))) {
        return intval(ob_passthru(str_replace(
            array("__DIR__"),
            array($dir),
            get_default("commands/__svnversion__", "cd __DIR__; svnversion")
        ), get_default("default/commandexpires", 60)));
    }
    // NOTHING TO DO
    return 0;
}

/*
 * GIT Version
 *
 * This function tries to return the git version of the project
 *
 * @dir => allow to specify where do you want to execute the gitversion command
 */
function gitversion($dir = ".")
{
    // USING REGULAR FILE
    if (file_exists("{$dir}/gitversion")) {
        return intval(file_get_contents("{$dir}/gitversion"));
    }
    // USING GIT
    if (check_commands(get_default("commands/gitversion", "git"), get_default("default/commandexpires", 60))) {
        return intval(ob_passthru(str_replace(
            array("__DIR__"),
            array($dir),
            get_default("commands/__gitversion__", "cd __DIR__; git rev-list HEAD --count")
        ), get_default("default/commandexpires", 60)));
    }
    // NOTHING TO DO
    return 0;
}

/*
 * IS PHP
 *
 * This function returns a boolean as a response about the comparison between the
 * version requested and the current version.
 *
 * @version => the version where do you want to compare
 */
function isphp($version)
{
    return version_compare(PHP_VERSION, strval($version), ">=");
}
