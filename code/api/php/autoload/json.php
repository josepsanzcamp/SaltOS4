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
 * Json helper module
 *
 * This fie contains useful functions related to colors
 */

/**
 * Terminal colors
 *
 * This define sets the colors array used in the next functions
 */
define('__COLORS_MAP__', [
    'reset' => "\e[0m",
    'red' => "\e[31m",
    'green' => "\e[32m",
    'blue' => "\e[34m",
    'magenta' => "\e[35m",
]);

/**
 * Json colorize
 *
 * This funcion is able to colorize a json fragment to dump into a tty terminal
 *
 * @json => the json code that you want to colorize
 *
 * Notes:
 *
 * This function uses a trick to convert numbers in scientific notation to an old
 * decimal style, to do it, detects numbers with the e letter and print using the
 * %.16f, this is used in sprintf to format floating-point numbers with 16 decimal
 * places, ensuring precision up to the typical limit of a double type in C, which
 * supports approximately 15-17 significant digits
 */
function json_colorize($json)
{
    extract(__COLORS_MAP__);
    $patterns = [
        '/(".*?")(:\s)/' => "$green$1$reset$2", // keys in green
        '/(:\s)(".*")/' => "$1$blue$2$reset", // strings in blue
        '/(:\s)(true|false|null)/' => "$1$red$2$reset", // booleans and null in red
        '/^(\s*?)(".*")/m' => "$1$blue$2$reset", // strings in blue
        '/^(\s*?)(true|false|null)/m' => "$1$red$2$reset", // booleans and null in red
    ];
    foreach ($patterns as $pattern => $replacement) {
        $json = preg_replace($pattern, $replacement, $json);
    }
    // Trick for numbers with scientific notation
    $patterns = [
        '/(:\s)([+-]?\d+(\.\d+)?([eE][+-]?\d+)?)/' => "$1$magenta$2$reset", // numbers in magenta
        '/^(\s*?)([+-]?\d+(\.\d+)?([eE][+-]?\d+)?)/m' => "$1$magenta$2$reset", // numbers in magenta
    ];
    foreach ($patterns as $pattern => $replacement) {
        $json = preg_replace_callback($pattern, function ($matches) use ($replacement) {
            if (is_numeric($matches[2]) && stripos($matches[2], 'e') !== false) {
                $matches[2] = rtrim(sprintf('%.16f', $matches[2]), '0');
            }
            return str_replace(['$1', '$2'], [$matches[1], $matches[2]], $replacement);
        }, $json);
    }
    return $json;
}
