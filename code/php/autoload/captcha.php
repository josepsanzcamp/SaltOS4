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
 * Captcha Is Prime Number
 *
 * This function is a detector of prime numbers, uses some optimizations and
 * ideas from www.polprimos.com, please, see the previous web to understand
 * the speedup of this function in the prime numbers validation
 *
 * @num => the number that you want to check if it is a primer numner
 *
 * Notes:
 *
 * See www.polprimos.com for understand it
 */
function __captcha_isprime($num)
{
    if ($num < 2) {
        return false;
    }
    if ($num % 2 == 0 && $num != 2) {
        return false;
    }
    if ($num % 3 == 0 && $num != 3) {
        return false;
    }
    if ($num % 5 == 0 && $num != 5) {
        return false;
    }
    // Primer numbers are distributed in 8 columns
    $div = 7;
    $max = intval(sqrt(floatval($num)));
    while (1) {
        if ($num % $div == 0 && $num != $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 4;
        if ($num % $div == 0 && $num != $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 2;
        if ($num % $div == 0 && $num != $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 4;
        if ($num % $div == 0 && $num != $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 2;
        if ($num % $div == 0 && $num != $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 4;
        if ($num % $div == 0 && $num != $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 6;
        if ($num % $div == 0 && $num != $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 2;
        if ($num % $div == 0 && $num != $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 6;
    }
    return true;
}

/**
 * Captcha Image
 *
 * This function returns an image with the code drawed in a background that
 * contains white noise to prevent that robots read the code
 *
 * @code => the code that you want to paint
 * @width => the width of the generated image
 * @height => the height of the generated image
 * @letter => the size of the letters of the generated image
 * @number => the size of the numbers of the generated image
 * @angle => the angle allowed to rotate the letters and numbers
 * @color => the color user to paint the code
 * @bgcolor => the background color of the image
 * @fgcolor => the color used to paint the letters of the background of the image
 * @period => parameter for the wave transformation
 * @amplitude => parameter for the wave transformation
 * @blur => true or false to enable or disable the blur effect
 *
 * Notes:
 *
 * The main idea to program this captcha was obtained from this post:
 * - http://sentidoweb.com/2007/01/03/laboratorio-ejemplo-de-captcha.php
 *
 * Too appear in ther posts if you search for it in google:
 * - http://www.google.es/search?q=captcha+alto_linea
 */
function __captcha_image($code, $args = array())
{
    $code = strval($code);
    $width = isset($args["width"]) ? $args["width"] : 90;
    $height = isset($args["height"]) ? $args["height"] : 45;
    $letter = isset($args["letter"]) ? $args["letter"] : 8;
    $number = isset($args["number"]) ? $args["number"] : 16;
    $angle = isset($args["angle"]) ? $args["angle"] : 10;
    $color = isset($args["color"]) ? $args["color"] : "5C8ED1";
    $bgcolor = isset($args["bgcolor"]) ? $args["bgcolor"] : "C8C8C8";
    $fgcolor = isset($args["fgcolor"]) ? $args["fgcolor"] : "B4B4B4";
    $period = isset($args["period"]) ? $args["period"] : 2;
    $amplitude = isset($args["amplitude"]) ? $args["amplitude"] : 8;
    $blur = isset($args["blur"]) ? $args["blur"] : "true";
    // Create the background image
    $im = imagecreatetruecolor($width, $height);
    $color2 = imagecolorallocate(
        $im,
        color2dec($color, "R"),
        color2dec($color, "G"),
        color2dec($color, "B")
    );
    $bgcolor2 = imagecolorallocate(
        $im,
        color2dec($bgcolor, "R"),
        color2dec($bgcolor, "G"),
        color2dec($bgcolor, "B")
    );
    $fgcolor2 = imagecolorallocate(
        $im,
        color2dec($fgcolor, "R"),
        color2dec($fgcolor, "G"),
        color2dec($fgcolor, "B")
    );
    imagefill($im, 0, 0, $bgcolor2);
    $letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
    $font = getcwd() . "/lib/fonts/GorriSans.ttf";
    $bbox = imagettfbbox($letter, 0, $font, $letters[0]);
    $heightline = abs($bbox[7] - $bbox[1]);
    $numlines = intval($height / $heightline) + 1;
    $maxletters = strlen($letters);
    for ($i = 0; $i < $numlines; $i++) {
        $posx = 0;
        $posy = ($heightline / 2) + ($heightline + $letter / 4) * $i;
        while ($posx < $width) {
            $oneletter = $letters[rand(0, $maxletters - 1)];
            $oneangle = rand(-$angle, $angle);
            $bbox = imagettfbbox($letter, $oneangle, $font, $oneletter);
            imagettftext($im, $letter, rand(-$angle, $angle), (int)$posx, (int)$posy, $fgcolor2, $font, $oneletter);
            $posx += $bbox[2] - $bbox[0] + $letter / 4;
        }
    }
    // Create the captcha code
    $im2 = imagecreatetruecolor($width, $height);
    $color2 = imagecolorallocate(
        $im2,
        color2dec($color, "R"),
        color2dec($color, "G"),
        color2dec($color, "B")
    );
    $bgcolor2 = imagecolorallocate(
        $im2,
        color2dec($bgcolor, "R"),
        color2dec($bgcolor, "G"),
        color2dec($bgcolor, "B")
    );
    $fgcolor2 = imagecolorallocate(
        $im2,
        color2dec($fgcolor, "R"),
        color2dec($fgcolor, "G"),
        color2dec($fgcolor, "B")
    );
    imagefill($im2, 0, 0, $bgcolor2);
    imagecolortransparent($im2, $bgcolor2);
    $angles = array();
    $widths = array();
    $heights = array();
    $widthsum = 0;
    for ($i = 0; $i < strlen($code); $i++) {
        $angles[$i] = rand(-$angle, $angle);
        $bbox = imagettfbbox($number, $angles[$i], $font, $code[$i]);
        $widths[$i] = abs($bbox[2] - $bbox[0]);
        $heights[$i] = abs($bbox[7] - $bbox[1]);
        $widthsum += $widths[$i];
    }
    $widthmiddle = $width / 2;
    $heightmiddle = $height / 2;
    $posx = $widthmiddle - $widthsum / 2;
    for ($i = 0; $i < strlen($code); $i++) {
        $posy = $heights[$i] / 2 + $heightmiddle;
        imagettftext($im2, $number, $angles[$i], (int)$posx, (int)$posy, $color2, $font, $code[$i]);
        $posx += $widths[$i];
    }
    // Copy the code to background using wave transformation
    $rel = M_PI / 180;
    $inia = rand(0, 360);
    $inib = rand(0, 360);
    for ($i = 0; $i < $width; $i++) {
        $a = sin((($i * $period) + $inia) * $rel) * $amplitude;
        for ($j = 0; $j < $height; $j++) {
            $b = sin((($j * $period) + $inib) * $rel) * $amplitude;
            if ($i + $b >= 0 && $i + $b < $width && $j + $a >= 0 && $j + $a < $height) {
                imagecopymerge($im, $im2, $i, $j, (int)($i + $b), (int)($j + $a), 1, 1, 100);
            }
        }
    }
    // Apply blur
    if (eval_bool($blur)) {
        if (function_exists("imagefilter")) {
            imagefilter($im, IMG_FILTER_GAUSSIAN_BLUR);
        }
    }
    // Continue
    ob_start();
    imagepng($im);
    $buffer = ob_get_clean();
    imagedestroy($im);
    imagedestroy($im2);
    return $buffer;
}

/**
 * Captcha Make Number function
 *
 * This function returns a random number of the desired length and as trick,
 * checks that the output is a prime number
 *
 * @length => the length of the desired output string
 */
function __captcha_make_number($length)
{
    do {
        $code = str_pad(strval(rand(0, pow(10, $length) - 1)), $length, "0", STR_PAD_LEFT);
    } while (!__captcha_isprime($code));
    return $code;
}

/**
 * Captcha Make Math Operation function
 *
 * This function returns a random math operation of the desired length and
 * as trick, checks that the output operation is performed by prime numbers
 *
 * @length => the length of the desired output string
 */
function __captcha_make_math($length)
{
    $max = pow(10, round($length / 2)) - 1;
    do {
        do {
            $num1 = rand(0, intval($max));
        } while (!__captcha_isprime($num1));
        $oper = rand(0, 1) ? "+" : "-";
        do {
            $num2 = rand(0, intval($max));
            $code = $num1 . $oper . $num2;
        } while (!__captcha_isprime($num2) || substr(strval($num2), 0, 1) == "7" || strlen($code) != $length);
    } while ($oper == "-" && $num1 < $num2);
    //~ $real = eval("return $code;");
    return $code;
}
