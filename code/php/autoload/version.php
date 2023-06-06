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

/*
 *
 */
function get_name_version_revision($copyright = false)
{
    $result = get_default("info/name", "SaltOS");
    $result .= " v" . get_default("info/version", "4.0");
    if (!is_array(get_default("info/revision", "SVN"))) {
        $result .= " r" . get_default("info/revision", "SVN");
    }
    if ($copyright) {
        $result .= ", " . get_default("info/copyright", "Copyright (C) 2007-2023 by Josep Sanz Campderrós");
    }
    return $result;
}

/*
 *
 */
function svnversion($dir = ".")
{
    if ($dir == "." && file_exists("../code")) {
        $dir = "../code";
    }
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
 *
 */
function gitversion($dir = ".")
{
    if ($dir == "." && file_exists("../code")) {
        $dir = "../code";
    }
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
 *
 */
function isphp($version)
{
    return version_compare(PHP_VERSION, strval($version), ">=");
}

/*
 *
 */
function ishhvm()
{
    return defined("HHVM_VERSION");
}

/*
 *
 */
function ismsie($version = null)
{
    $useragent = get_server("HTTP_USER_AGENT");
    if ($version === null) {
        return strpos($useragent, "MSIE") !== false;
    } elseif (is_string($version)) {
        return strpos($useragent, "MSIE {$version}") !== false;
    } elseif (is_array($version)) {
        foreach ($version as $v) {
            if (strpos($useragent, "MSIE {$v}") !== false) {
                return true;
            }
        }
        return false;
    }
}
