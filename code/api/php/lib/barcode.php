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
 * Barcode helper module
 *
 * This fie contains useful functions related to barcodes
 */

/**
 * BarCode image function
 *
 * This function allow to generate a barcode, you can pass the desired
 * message that you want to convert in barcode and it returns an image
 * with the data
 *
 * @msg => Contents of the barcode
 * @w   => width of each unit's bar of the barcode
 * @h   => height of the barcode (without margins and text footer)
 * @m   => margin of the barcode (white area that surround the barcode)
 * @s   => size of the footer text, not used if zero
 * @t   => type of the barcode, C128 is the most common type used
 *
 * Notes:
 *
 * The normal behavior is returns a png image, but if something was wrong,
 * the function can returns an empty string
 */
function __barcode_image($msg, $w, $h, $m, $s, $t)
{
    $png = __barcode_image_png($msg, $w, $h, $t);
    if (!$png) {
        return $png;
    }
    $im1 = imagecreatefromstring($png);
    $width = imagesx($im1);
    $height = imagesy($im1);
    $extra = $s;
    if ($s) {
        $font = getcwd() . '/lib/fonts/DejaVuSans.ttf';
        $bbox = imagettfbbox($s, 0, $font, $msg);
        $extra = abs($bbox[5] - $bbox[1]) + $m;
    }
    $im2 = imagecreatetruecolor($width + 2 * $m, $height + 2 * $m + $extra);
    $bgcol = imagecolorallocate($im2, 255, 255, 255);
    imagefilledrectangle($im2, 0, 0, $width + 2 * $m, $height + 2 * $m + $extra, $bgcol);
    imagecopy($im2, $im1, $m, $m, 0, 0, $width, $height);
    if ($s) {
        // add msg to the image footer
        $px = ($width + 2 * $m) / 2 - ($bbox[4] - $bbox[0]) / 2;
        $py = $m + $h + 1 + $m + $s;
        $fgcol = imagecolorallocate($im2, 0, 0, 0);
        imagettftext($im2, $s, 0, (int)$px, (int)$py, $fgcol, $font, $msg);
    }
    ob_start();
    imagepng($im2);
    $buffer = ob_get_clean();
    imagedestroy($im1);
    imagedestroy($im2);
    return $buffer;
}

/**
 * BarCode image png function
 *
 * This function allow to generate a barcode, you can pass the desired
 * message that you want to convert in barcode and it returns an image
 * with the data
 *
 * @msg => Contents of the barcode
 * @w   => width of each unit's bar of the barcode
 * @h   => height of the barcode (without margins and text footer)
 * @t   => type of the barcode, C128 is the most common type used
 *
 * Notes:
 *
 * The normal behavior is returns a png image without margins and text,
 * but if something was wrong, the function can returns an empty string
 */
function __barcode_image_png($msg, $w, $h, $t)
{
    require_once 'lib/tcpdf/vendor/autoload.php';
    $barcode = new TCPDFBarcode($msg, $t);
    $array = $barcode->getBarcodeArray();
    if (!isset($array['maxw'])) {
        return '';
    }
    $buffer = $barcode->getBarcodePngData($w, $h);
    return $buffer;
}

/**
 * BarCode image svg function
 *
 * This function allow to generate a barcode, you can pass the desired
 * message that you want to convert in barcode and it returns an image
 * with the data
 *
 * @msg => Contents of the barcode
 * @w   => width of each unit's bar of the barcode
 * @h   => height of the barcode (without margins and text footer)
 * @t   => type of the barcode, C128 is the most common type used
 *
 * Notes:
 *
 * The normal behavior is returns a svg image without margins and text,
 * but if something was wrong, the function can returns an empty string
 */
function __barcode_image_svg($msg, $w, $h, $t)
{
    require_once 'lib/tcpdf/vendor/autoload.php';
    $barcode = new TCPDFBarcode($msg, $t);
    $array = $barcode->getBarcodeArray();
    if (!isset($array['maxw'])) {
        return '';
    }
    $buffer = $barcode->getBarcodeSVGcode($w, $h);
    return $buffer;
}
