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
 * QRCode helper module
 *
 * This fie contains useful functions related to QRCodes
 */

/**
 * QRCode image function
 *
 * This function allow to generate a qrcode with the SaltOS logo embedded
 * in the center of the image, you can pass the desired message that you
 * want to convert in qrcode and it returns an image with the data
 *
 * @msg => Contents of the qrcode
 * @s   => size of each pixel used in the qrcode
 * @m   => margin of the qrcode (white area that that surround the qrcode)
 * @l   => error correction: L (low), M (medium), Q (better), H (best)
 *
 * Notes:
 *
 * The normal behavior is returns a png image, but if something was wrong,
 * the function can returns an empty string
 */
function __qrcode_image($msg, $s, $m, $l)
{
    require_once __ROOT__ . 'lib/tcpdf/vendor/autoload.php';
    $barcode = new TCPDF2DBarcode($msg, "QRCODE,$l");
    $array = $barcode->getBarcodeArray();
    if (!isset($array['num_cols']) || !isset($array['num_rows'])) {
        return '';
    }
    $width = ($array['num_cols'] * $s);
    $height = ($array['num_rows'] * $s);
    $im = imagecreatetruecolor($width + 2 * $m, $height + 2 * $m);
    $bgcol = imagecolorallocate($im, 255, 255, 255);
    imagefilledrectangle($im, 0, 0, $width + 2 * $m, $height + 2 * $m, $bgcol);
    $fgcol = imagecolorallocate($im, 0, 0, 0);
    foreach ($array['bcode'] as $key => $val) {
        foreach ($val as $key2 => $val2) {
            if ($val2) {
                imagefilledrectangle(
                    $im,
                    (int)($key2 * $s + $m),
                    (int)($key * $s + $m),
                    (int)(($key2 + 1) * $s + $m - 1),
                    (int)(($key + 1) * $s + $m - 1),
                    $fgcol
                );
            }
        }
    }
    ob_start();
    imagepng($im);
    $buffer = ob_get_clean();
    imagedestroy($im);
    return $buffer;
}
