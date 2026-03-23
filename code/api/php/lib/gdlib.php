<?php

/**
 *  ____        _ _    ___  ____  _  _
 * / ___|  __ _| | |_ / _ \/ ___|| || |
 * \___ \ / _` | | __| | | \___ \| || |_
 *  ___) | (_| | | |_| |_| |___) |__   _|
 * |____/ \__,_|_|\__|\___/|____/   |_|
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
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
    $font = getcwd() . '/lib/atkinson/fonts/AtkinsonHyperlegibleNext-Regular.otf';
    $bbox = imagettfbbox($size, 0, $font, $text);
    $width = abs($bbox[4] - $bbox[0]);
    return $width;
}

/**
 * Image resize
 *
 * This function is a helper for the html functions, and is intended to
 * get images less than 1000x1000 pixels, to do it, maintain the width and
 * height relation, the main idea is to get images scaled less that the size
 * parameter
 *
 * @data => the data of the image
 * @size => the size used in the control (size x size)
 */
function image_resize($data, $size)
{
    // I have detected that imagecreatefromstring generates uncontrolable
    // errors, for this reason, I have overloaded the error handler to
    // manage this kind of errors
    overload_error_handler('imagecreatefromstring');
    $im = imagecreatefromstring($data);
    restore_error_handler();
    // End of the overloaded error zone
    if (!$im) {
        return $data;
    }
    $width = imagesx($im);
    $height = imagesy($im);
    if ($width <= $size && $height <= $size) {
        imagedestroy_deprecated($im);
        return $data;
    }
    $scale = min($size / $width, $size / $height);
    $new_width = (int)($width * $scale);
    $new_height = (int)($height * $scale);
    $im2 = imagecreatetruecolor($new_width, $new_height);
    // Fill the new image with white to avoid black background on transparency
    $white = imagecolorallocate($im2, 255, 255, 255);
    imagefilledrectangle($im2, 0, 0, $new_width, $new_height, $white);
    imagecopyresampled($im2, $im, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    ob_start();
    imagejpeg($im2);
    $img = ob_get_clean();
    imagedestroy_deprecated($im);
    imagedestroy_deprecated($im2);
    return $img;
}
