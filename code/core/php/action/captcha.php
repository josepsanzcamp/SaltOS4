<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2023 by Josep Sanz Campderrós
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
 */

$user_id = current_user();
if (!$user_id) {
    show_json_error("Permission denied");
}

// Check parameters
foreach (["type", "format"] as $key) {
    if (get_data("json/$key") == "") {
        show_json_error("$key not found or void");
    }
}
$type = get_data("json/type");
if (!in_array($type, ["number", "math"])) {
    show_json_error("Unknown type $type");
}
$format = get_data("json/format");
if (!in_array($format, ["png", "json"])) {
    show_json_error("Unknown format $format");
}

$length = get_data("json/length") ? get_data("json/length") : 5;
$args = [];
$args["width"] = get_data("json/width") ? get_data("json/width") : 180;
$args["height"] = get_data("json/height") ? get_data("json/height") : 90;
$args["letter"] = get_data("json/letter") ? get_data("json/letter") : 16;
$args["number"] = get_data("json/number") ? get_data("json/number") : 32;
$args["angle"] = get_data("json/angle") ? get_data("json/angle") : 10;
$args["color"] = get_data("json/color") ? get_data("json/color") : "5c8ed1";
$args["bgcolor"] = get_data("json/bgcolor") ? get_data("json/bgcolor") : "c8c8c8";
$args["fgcolor"] = get_data("json/fgcolor") ? get_data("json/fgcolor") : "b4b4b4";
$args["period"] = get_data("json/period") ? get_data("json/period") : 2;
$args["amplitude"] = get_data("json/amplitude") ? get_data("json/amplitude") : 8;
$args["blur"] = get_data("json/blur") ? get_data("json/blur") : "true";

if ($type == "number") {
    $code = __captcha_make_number($length);
}
if ($type == "math") {
    $code = __captcha_make_math($length);
}
$image = __captcha_image($code, $args);
if ($format == "png") {
    output_handler([
        "data" => $image,
        "type" => "image/png",
        "cache" => false,
    ]);
}
$data = "data:image/png;base64," . base64_encode($image);
$result = [
    "code" => $code,
    "image" => $data,
];
output_handler_json($result);
