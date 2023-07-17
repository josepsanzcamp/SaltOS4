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

/**
 * Color To Dec function
 *
 * This function is a helper that allow to get from a RGB hex color the value
 * in decimal of the specified component, usefull to get the amount of color
 * red, green or blue in decimal base from an string
 *
 * Is able to understand colors with the formats #abcdef, abcdef, #000, #fff
 *
 * @color     => The color that you want to parse
 * @component => The component that you want to retrieve their value
 */
function color2dec($color, $component)
{
    if (substr($color, 0, 1) == "#") {
        $color = substr($color, 1);
    }
    if (strlen($color) == 3) {
        $R = substr($color, 0, 1);
        $G = substr($color, 1, 1);
        $B = substr($color, 2, 1);
        $color = $R . $R . $G . $G . $B . $B;
    }
    if (strlen($color) != 6) {
        show_php_error(["phperror" => "Unknown color length"]);
    }
    $offset = ["R" => 0, "G" => 2, "B" => 4];
    if (!isset($offset[$component])) {
        show_php_error(["phperror" => "Unknown color component"]);
    }
    return hexdec(substr($color, $offset[$component], 2));
}
