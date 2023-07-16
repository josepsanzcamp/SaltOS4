<?php

/**
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
 * Score Image function
 *
 * This function generates an image with a gradient from red to yellos and
 * then, to green, depending of the score passed to the function, the params
 * allos to define the size of the image or the size of the font used to
 * write the score percent
 *
 * @score  => a number between 0 and 100
 * @width  => the width of the generated image
 * @height => the height of the generated image
 * @size   => the size of the font of the generated image
 */
function __score_image($score, $width, $height, $size)
{
    $im = imagecreatetruecolor($width, $height);
    $incr = ($score * 512 / 100) / $width;
    $posx = 0;
    for ($i = 0; $i <= 255; $i = $i + $incr) {
        if ($posx > $width) {
            break;
        }
        $color = imagecolorallocate($im, 255, (int)$i, 0);
        imageline($im, $posx, 0, $posx, $height, $color);
        $posx++;
    }
    for ($i = 255; $i >= 0; $i = $i - $incr) {
        if ($posx > $width) {
            break;
        }
        $color = imagecolorallocate($im, (int)$i, 255, 0);
        imageline($im, $posx, 0, $posx, $height, $color);
        $posx++;
    }
    $font = getcwd() . "/lib/fonts/DejaVuSans.ttf";
    $bbox = imagettfbbox($size, 0, $font, $score . "%");
    $sx = $bbox[4] - $bbox[0];
    $sy = $bbox[5] - $bbox[1];
    $color = imagecolorallocate($im, 0, 0, 0);
    imagettftext($im, $size, 0, (int)($width / 2 - $sx / 2), (int)($height / 2 - $sy / 2), $color, $font, $score . "%");
    // CONTINUE
    ob_start();
    imagepng($im);
    $buffer = ob_get_clean();
    imagedestroy($im);
    return $buffer;
}
