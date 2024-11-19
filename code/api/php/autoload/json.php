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
 * Json helper module
 *
 * This fie contains useful functions related to colors
 */

/**
 * Json colorize
 *
 * This funcion is able to colorize a json fragment to dump into a tty terminal
 *
 * @json => the json code that you want to colorize
 */
function json_colorize($json)
{
    $patterns = [
        '/(".*?")(:\s)/' => "\e[32m$1\e[0m$2", // keys in green
        '/(:\s)(".*")/' => "$1\e[34m$2\e[0m", // strings in blue
        '/(:\s)(\d+(\.\d+)?([eE][+-]?\d+)?)/' => "$1\e[35m$2\e[0m", // numbers in magenta
        '/(:\s)(true|false|null)/' => "$1\e[31m$2\e[0m", // booleans and null in red
        '/^(\s*?)(".*")/m' => "$1\e[34m$2\e[0m", // strings in blue
        '/^(\s*?)(\d+(\.\d+)?([eE][+-]?\d+)?)/m' => "$1\e[35m$2\e[0m", // numbers in magenta
        '/^(\s*?)(true|false|null)/m' => "$1\e[31m$2\e[0m", // booleans and null in red
    ];
    foreach ($patterns as $pattern => $replacement) {
        $json = preg_replace($pattern, $replacement, $json);
    }
    return $json;
}
