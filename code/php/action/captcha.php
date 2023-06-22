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
 * TODO
 */

$_SERVER["HTTP_TOKEN"] = "e9f3ebd0-8e73-e4c4-0ebd-7056cf0e70fe";
$_SERVER["REMOTE_ADDR"] = "127.0.0.1";
$_SERVER["HTTP_USER_AGENT"] = "curl/7.74.0";
$_DATA["json"]["type"] = "number";
$_DATA["json"]["format"] = "json";

$user_id = current_user();
if (!$user_id) {
    show_json_error("authentication error");
}

// Check parameters
foreach (array("type","format") as $key) {
    if (!isset($_DATA["json"][$key]) || $_DATA["json"][$key] == "") {
        show_json_error("$key not found or void");
    }
}
$type = $_DATA["json"]["type"];
if (!in_array($type, array("number","math"))) {
    show_json_error("unknown type $type");
}
$format = $_DATA["json"]["format"];
if (!in_array($format, array("png","json"))) {
    show_json_error("unknown format $format");
}

$length = isset($_DATA["json"]["length"]) ? $_DATA["json"]["length"] : 5;
$args = array();
$args["width"] = isset($_DATA["json"]["width"]) ? $_DATA["json"]["width"] : 90;
$args["height"] = isset($_DATA["json"]["height"]) ? $_DATA["json"]["height"] : 45;
$args["letter"] = isset($_DATA["json"]["letter"]) ? $_DATA["json"]["letter"] : 8;
$args["number"] = isset($_DATA["json"]["number"]) ? $_DATA["json"]["number"] : 16;
$args["angle"] = isset($_DATA["json"]["angle"]) ? $_DATA["json"]["angle"] : 10;
$args["color"] = isset($_DATA["json"]["color"]) ? $_DATA["json"]["color"] : "5C8ED1";
$args["bgcolor"] = isset($_DATA["json"]["bgcolor"]) ? $_DATA["json"]["bgcolor"] : "C8C8C8";
$args["fgcolor"] = isset($_DATA["json"]["fgcolor"]) ? $_DATA["json"]["fgcolor"] : "B4B4B4";
$args["period"] = isset($_DATA["json"]["period"]) ? $_DATA["json"]["period"] : 2;
$args["amplitude"] = isset($_DATA["json"]["amplitude"]) ? $_DATA["json"]["amplitude"] : 8;
$args["blur"] = isset($_DATA["json"]["blur"]) ? $_DATA["json"]["blur"] : "true";

if ($type == "number") {
    $code = __captcha_make_number($length);
}
if ($type == "math") {
    $code = __captcha_make_math($length);
}
$image = __captcha_image($code, $args);
if ($format == "png") {
    output_handler(array(
        "data" => $image,
        "type" => "image/png",
        "cache" => false
    ));
}
$data = base64_encode($image);
$data = "data:image/png;base64,{$data}";
$result = array(
    "code" => $code,
    "image" => $data,
);
output_handler_json($result);
