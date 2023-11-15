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
 */

$user_id = current_user();
if (!$user_id) {
    show_json_error("Permission denied");
}

// Check parameters
foreach (["msg", "format"] as $key) {
    if (get_data("json/$key") == "") {
        show_json_error("$key not found or void");
    }
}
$msg = get_data("json/msg");
$format = get_data("json/format");
if (!in_array($format, ["png", "json"])) {
    show_json_error("Unknown format $format");
}

$s = get_data("json/s") ? get_data("json/s") : 6;
$m = get_data("json/m") ? get_data("json/m") : 10;

$image = __qrcode($msg, $s, $m);
if ($image == "") {
    show_json_error("Internal error");
}
if ($format == "png") {
    output_handler([
        "data" => $image,
        "type" => "image/png",
        "cache" => false,
    ]);
}
$data = "data:image/png;base64," . base64_encode($image);
$result = [
    "msg" => $msg,
    "image" => $data,
];
output_handler_json($result);
