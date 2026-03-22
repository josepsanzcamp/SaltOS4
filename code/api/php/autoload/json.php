<?php

/**
 *  ____        _ _    ___  ____    _  _    _
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / |
 * \___ \ / _` | | __| | | \___ \  | || |_ | |
 *  ___) | (_| | | |_| |_| |___) | |__   _|| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)_|
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
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
    'grey'  => "\e[90m",
    'green' => "\e[32m",
    'blue'  => "\e[1;34m",
    'white' => "\e[97m",
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

    $stringPattern = '"(?:\\\\.|[^"\\\\])*"';
    $numberPattern = '[+-]?\d+(?:\.\d+)?(?:[eE][+-]?\d+)?';

    $patterns = [
        "/($stringPattern)(\\s*:)/"  => "$blue$1$reset$2", // keys
        "/(:\\s*)($stringPattern)/"  => "$1$green$2$reset", // strings value
        "/^(\\s*)($stringPattern)/m" => "$1$green$2$reset", // strings in array
        "/(:\\s*)(null)\\b/"         => "$1$grey$2$reset",
        "/^(\\s*)(null)\\b/m"        => "$1$grey$2$reset",
        "/(:\\s*)(true|false)\\b/"   => "$1$white$2$reset",
        "/^(\\s*)(true|false)\\b/m"  => "$1$white$2$reset",
    ];

    foreach ($patterns as $pattern => $replacement) {
        $json = preg_replace($pattern, $replacement, $json);
    }

    // Trick for numbers with scientific notation
    $patterns = [
        "/(:\\s*)($numberPattern)/"  => "$1$white$2$reset",
        "/^(\\s*)($numberPattern)/m" => "$1$white$2$reset",
    ];

    foreach ($patterns as $pattern => $replacement) {
        $json = preg_replace_callback($pattern, function ($m) use ($replacement) {
            if (stripos($m[2], 'e') !== false) {
                $m[2] = rtrim(rtrim(sprintf('%.16f', (float)$m[2]), '0'), '.');
            }
            return str_replace(['$1', '$2'], [$m[1], $m[2]], $replacement);
        }, $json);
    }

    return $json;
}
