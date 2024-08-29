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
 * BarCode action
 *
 * This action allow to generate a barcode, you can pass the desired
 * message that you want to convert in barcode
 *
 * @msg    => the msg that you want to codify in the qrcode
 * @format => the format used to the result, only can be png or json
 *
 * @w => width of each unit's bar of the barcode
 * @h => height of the barcode (without margins and text footer)
 * @m => margin of the barcode (white area that surround the barcode)
 * @s => size of the footer text, not used if zero
 * @t => type of the barcode, C128 is the most common type used
 *
 * QRCode action
 *
 * This action allow to generate a qrcode with the SaltOS logo embedded
 * in the center of the image, you can pass the desired message that you
 * want to convert in qrcode.
 *
 * @msg    => the msg that you want to codify in the qrcode
 * @format => the format used to the result, only can be png or json
 *
 * @s => size of each pixel used in the qrcode
 * @m => margin of the qrcode (white area that that surround the qrcode)
 * @l => error correction: L (low), M (medium), Q (better), H (best)
 *
 * Captcha action
 *
 * This action allo to retrieve the captcha of a randomly number or math
 * operation, used to prevent massive requests, can perform the action of
 * create the captcha image and return the result as a simple image or as
 * a json image
 *
 * @type   => the type used to the result, only can be number or math
 * @format => the format used to the result, only can be png or json
 *
 * @width     => the width of the generated image
 * @height    => the height of the generated image
 * @letter    => the size of the letters of the generated image
 * @number    => the size of the numbers of the generated image
 * @angle     => the angle allowed to rotate the letters and numbers
 * @color     => the color user to paint the code
 * @bgcolor   => the background color of the image
 * @fgcolor   => the color used to paint the letters of the background of the image
 * @period    => parameter for the wave transformation
 * @amplitude => parameter for the wave transformation
 * @blur      => true or false to enable or disable the blur effect
 *
 * Score action
 *
 * This action allo to retrieve the score of a password, intended to be used
 * as helper previously to the authupdate call, can perform the action of
 * compute the score and return the result as a simple image or as a json
 * image
 *
 * @pass   => the password that you want to compute the score
 * @format => the format used to the result, only can be png or json
 *
 * @width  => the width of the generated image
 * @height => the height of the generated image
 * @size   => the size of the font of the generated image
 */

db_connect();
$user_id = current_user();
if (!$user_id) {
    show_json_error('Permission denied');
}

// Check parameters
$format = get_data('json/format');
if (!in_array($format, ['png', 'json'])) {
    show_json_error("Unknown format $format");
}

$output = [];
$action = get_data('rest/1');
switch ($action) {
    case 'barcode':
        // Check parameters
        $msg = get_data('json/msg');
        if ($msg == '') {
            show_json_error('msg not found');
        }
        // Prepare parameters
        $w = get_data('json/w') ? get_data('json/w') : 1;
        $h = get_data('json/h') ? get_data('json/h') : 30;
        $m = get_data('json/m') ? get_data('json/m') : 10;
        $s = get_data('json/s') ? get_data('json/s') : 8;
        $t = get_data('json/t') ? get_data('json/t') : 'C39';
        // Do image
        require_once 'php/lib/barcode.php';
        $image = __barcode_image($msg, $w, $h, $m, $s, $t);
        $output = [
            'msg' => $msg,
        ];
        break;
    case 'qrcode':
        // Check parameters
        $msg = get_data('json/msg');
        if ($msg == '') {
            show_json_error('msg not found');
        }
        // Prepare parameters
        $s = get_data('json/s') ? get_data('json/s') : 6;
        $m = get_data('json/m') ? get_data('json/m') : 10;
        $l = get_data('json/l') ? get_data('json/l') : 'L';
        // Do image
        require_once 'php/lib/qrcode.php';
        $image = __qrcode_image($msg, $s, $m, $l);
        $output = [
            'msg' => $msg,
        ];
        break;
    case 'captcha':
        // Check parameters
        $type = get_data('json/type');
        if (!in_array($type, ['number', 'math'])) {
            show_json_error("Unknown type $type");
        }
        // Prepare parameters
        $length = get_data('json/length') ? get_data('json/length') : 5;
        $args = [];
        $args['width'] = get_data('json/width') ? get_data('json/width') : 180;
        $args['height'] = get_data('json/height') ? get_data('json/height') : 90;
        $args['letter'] = get_data('json/letter') ? get_data('json/letter') : 16;
        $args['number'] = get_data('json/number') ? get_data('json/number') : 32;
        $args['angle'] = get_data('json/angle') ? get_data('json/angle') : 10;
        $args['color'] = get_data('json/color') ? get_data('json/color') : '5c8ed1';
        $args['bgcolor'] = get_data('json/bgcolor') ? get_data('json/bgcolor') : 'c8c8c8';
        $args['fgcolor'] = get_data('json/fgcolor') ? get_data('json/fgcolor') : 'b4b4b4';
        $args['period'] = get_data('json/period') ? get_data('json/period') : 2;
        $args['amplitude'] = get_data('json/amplitude') ? get_data('json/amplitude') : 8;
        $args['blur'] = get_data('json/blur') ? get_data('json/blur') : 'true';
        // Do image
        require_once 'php/lib/captcha.php';
        if ($type == 'number') {
            $code = __captcha_make_number($length);
        }
        if ($type == 'math') {
            $code = __captcha_make_math($length);
        }
        $image = __captcha_image($code, $args);
        $output = [
            'code' => $code,
        ];
        break;
    case 'score':
        // Check parameters
        $pass = get_data('json/pass');
        if ($pass == '') {
            show_json_error('pass not found');
        }
        // Prepare parameters
        $width = get_data('json/width') ? get_data('json/width') : 60;
        $height = get_data('json/height') ? get_data('json/height') : 16;
        $size = get_data('json/size') ? get_data('json/size') : 8;
        // Do image
        require_once 'php/lib/password.php';
        require_once 'php/lib/score.php';
        $score = password_strength($pass);
        $image = __score_image($score, $width, $height, $size);
        $minscore = intval(get_config('auth/passwordminscore'));
        $valid = ($score >= $minscore) ? 'ok' : 'ko';
        $output = [
            'score' => $score . '%',
            'valid' => $valid,
        ];
        break;
    default:
        show_php_error(['phperror' => "Unknown action $action"]);
}

// Check image
if ($image == '') {
    show_json_error('Internal error');
}
// Dump image
if ($format == 'png') {
    output_handler([
        'data' => $image,
        'type' => 'image/png',
        'cache' => false,
    ]);
}
// Dump json
output_handler_json(array_merge([
    'image' => mime_inline('image/png', $image),
], $output));
