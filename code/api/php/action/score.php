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
    show_json_error("Permission denied");
}

// Check parameters
foreach (["pass", "format"] as $key) {
    if (get_data("json/$key") == "") {
        show_json_error("$key not found or void");
    }
}
$pass = get_data("json/pass");
$format = get_data("json/format");
if (!in_array($format, ["png", "json"])) {
    show_json_error("Unknown format $format");
}

$width = get_data("json/width") ? get_data("json/width") : 60;
$height = get_data("json/height") ? get_data("json/height") : 16;
$size = get_data("json/size") ? get_data("json/size") : 8;

require_once "php/lib/password.php";
require_once "php/lib/score.php";
$score = password_strength($pass);
$image = __score_image($score, $width, $height, $size);
if ($format == "png") {
    output_handler([
        "data" => $image,
        "type" => "image/png",
        "cache" => false,
    ]);
}
$minscore = intval(get_config("auth/passwordminscore"));
$valid = ($score >= $minscore) ? "ok" : "ko";
output_handler_json([
    "score" => $score . "%",
    "image" => mime_inline("image/png", $image),
    "valid" => $valid,
]);
