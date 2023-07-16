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
 * Score Action
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

$user_id = current_user();
if (!$user_id) {
    show_json_error("authentication error");
}

// Check parameters
foreach (["pass", "format"] as $key) {
    if (!isset($_DATA["json"][$key]) || $_DATA["json"][$key] == "") {
        show_json_error("$key not found or void");
    }
}
$pass = $_DATA["json"]["pass"];
$format = $_DATA["json"]["format"];
if (!in_array($format, ["png", "json"])) {
    show_json_error("unknown format $format");
}

$width = isset($_DATA["json"]["width"]) ? $_DATA["json"]["width"] : 60;
$height = isset($_DATA["json"]["height"]) ? $_DATA["json"]["height"] : 16;
$size = isset($_DATA["json"]["size"]) ? $_DATA["json"]["size"] : 8;

$score = password_strength($pass);
$image = __score_image($score, $width, $height, $size);
if ($format == "png") {
    output_handler([
        "data" => $image,
        "type" => "image/png",
        "cache" => false
    ]);
}
$data = "data:image/png;base64," . base64_encode($image);
$minscore = current_datetime(get_config("auth/passwordminscore"));
$valid = ($score >= $minscore) ? 1 : 0;
$result = [
    "score" => $score . "%",
    "image" => $data,
    "valid" => $valid,
];
output_handler_json($result);
