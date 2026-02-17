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
 *
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
 */

declare(strict_types=1);

/**
 * Color helper module
 *
 * This fie contains useful functions related to colors
 */

/**
 * Color To Dec function
 *
 * This function is a helper that allow to get from a RGB hex color the value
 * in decimal of the specified component, useful to get the amount of color
 * red, green or blue in decimal base from an string
 *
 * Is able to understand colors with the formats #abcdef, abcdef, #000, #fff
 *
 * @color     => The color that you want to parse
 * @component => The component that you want to retrieve their value
 */
function color2dec($color, $component)
{
    if (substr($color, 0, 1) === '#') {
        $color = substr($color, 1);
    }
    if (strlen($color) === 3) {
        $R = substr($color, 0, 1);
        $G = substr($color, 1, 1);
        $B = substr($color, 2, 1);
        $color = $R . $R . $G . $G . $B . $B;
    }
    if (strlen($color) !== 6) {
        show_php_error(['phperror' => 'Unknown color length']);
    }
    $offset = ['R' => 0, 'G' => 2, 'B' => 4];
    $component = strtoupper(strval($component));
    if (!isset($offset[$component])) {
        show_php_error(['phperror' => 'Unknown color component']);
    }
    return hexdec(substr($color, $offset[$component], 2));
}
