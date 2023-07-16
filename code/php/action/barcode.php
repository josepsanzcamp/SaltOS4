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
 * BarCode Action
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
 */

$user_id = current_user();
if (!$user_id) {
    show_json_error("authentication error");
}

// Check parameters
foreach (["msg", "format"] as $key) {
    if (!isset($_DATA["json"][$key]) || $_DATA["json"][$key] == "") {
        show_json_error("$key not found or void");
    }
}
$msg = $_DATA["json"]["msg"];
$format = $_DATA["json"]["format"];
if (!in_array($format, ["png", "json"])) {
    show_json_error("unknown format $format");
}

$w = isset($_DATA["json"]["w"]) ? $_DATA["json"]["w"] : 1;
$h = isset($_DATA["json"]["h"]) ? $_DATA["json"]["h"] : 30;
$m = isset($_DATA["json"]["m"]) ? $_DATA["json"]["m"] : 10;
$s = isset($_DATA["json"]["s"]) ? $_DATA["json"]["s"] : 8;
$t = isset($_DATA["json"]["t"]) ? $_DATA["json"]["t"] : "C39";

$image = __barcode($msg, $w, $h, $m, $s, $t);
if ($image == "") {
    show_json_error("internal error");
}
if ($format == "png") {
    output_handler([
        "data" => $image,
        "type" => "image/png",
        "cache" => false
    ]);
}
$data = "data:image/png;base64," . base64_encode($image);
$result = [
    "msg" => $msg,
    "image" => $data,
];
output_handler_json($result);
