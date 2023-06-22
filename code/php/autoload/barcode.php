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

/*
    Name:
        __barcode
    Abstract:
        This function generates a barcode image
    Input:
        - msg: Contents of the barcode
        - w: width of each unit's bar of the barcode
        - h: height of the barcode (without margins and text footer)
        - m: margin of the barcode (white area that surround the barcode)
        - s: size of the footer text, not used if zero
        - t: type of the barcode, C128 is the most common type used
    Output:
        - The png contents of the generated barcode image
        - Otherwise, an empty string if something was wrong
*/
function __barcode($msg, $w, $h, $m, $s, $t)
{
    require_once "lib/tcpdf/vendor/autoload.php";
    $barcode = new TCPDFBarcode($msg, $t);
    $array = $barcode->getBarcodeArray();
    if (!isset($array["maxw"])) {
        return "";
    }
    $width = $array["maxw"] * $w;
    $height = $h;
    $extra = $s;
    if ($s) {
        $font = getcwd() . "/lib/fonts/DejaVuSans.ttf";
        $bbox = imagettfbbox($s, 0, $font, $msg);
        $extra = abs($bbox[5] - $bbox[1]) + $m;
    }
    $im = imagecreatetruecolor($width + 2 * $m, $height + 2 * $m + $extra);
    $bgcol = imagecolorallocate($im, 255, 255, 255);
    imagefilledrectangle($im, 0, 0, $width + 2 * $m, $height + 2 * $m + $extra, $bgcol);
    $fgcol = imagecolorallocate($im, 0, 0, 0);
    $x = 0;
    foreach ($array["bcode"] as $key => $val) {
        $bw = round(($val["w"] * $w), 3);
        $bh = round(($val["h"] * $h / $array["maxh"]), 3);
        if ($val["t"]) {
            $y = round(($val["p"] * $h / $array["maxh"]), 3);
            imagefilledrectangle(
                $im,
                intval($x + $m),
                intval($y + $m),
                intval(($x + $bw - 1) + $m),
                intval(($y + $bh - 1) + $m),
                $fgcol
            );
        }
        $x += $bw;
    }
    if ($s) {
        // ADD MSG TO THE IMAGE FOOTER
        $px = ($width + 2 * $m) / 2 - ($bbox[4] - $bbox[0]) / 2;
        $py = $m + $h + 1 + $m + $s;
        imagettftext($im, $s, 0, (int)$px, (int)$py, $fgcol, $font, $msg);
    }
    // CONTINUE
    ob_start();
    imagepng($im);
    $buffer = ob_get_clean();
    imagedestroy($im);
    return $buffer;
}
