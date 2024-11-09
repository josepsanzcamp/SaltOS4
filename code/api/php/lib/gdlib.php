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
 * GD utils helper module
 *
 * This fie contains useful functions related to the GD library
 */

/**
 * Compute width
 *
 * This function uses the GD library to compute the width of a text,
 * can contains newlines and the returned value is the width of the
 * bounring box required to print the text using the font and size
 * specified
 *
 * @text => the text that you want to compute the width
 * @size => the size used in the render process
 *
 * Notes:
 *
 * As the default font in saltos is the Atkinson Hyperlegible, in this
 * function was set as default font and you can not replace at the
 * moment
 */
function compute_width($text, $size)
{
    $font = getcwd() . '/lib/atkinson-hyperlegible/fonts/Atkinson-Hyperlegible-Regular-102.otf';
    $bbox = imagettfbbox($size, 0, $font, $text);
    $width = abs($bbox[4] - $bbox[0]);
    return $width;
}

/**
 * TODO
 *
 * TODO
 */
function image_resize($data, $size)
{
    // The following @ is to suppress a unit test warning when invalid data is found
    $im = @imagecreatefromstring($data);
    if (!$im) {
        return $data;
    }
    $width = imagesx($im);
    $height = imagesy($im);
    if ($width <= $size && $height <= $size) {
        return $data;
    }
    $scale = min($size / $width, $size / $height);
    $new_width = (int)($width * $scale);
    $new_height = (int)($height * $scale);
    $im2 = imagecreatetruecolor($new_width, $new_height);
    imagecopyresampled($im2, $im, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    ob_start();
    imagejpeg($im2);
    $img = ob_get_clean();
    imagedestroy($im);
    imagedestroy($im2);
    return $img;
}
