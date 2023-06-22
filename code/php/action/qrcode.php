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
$_DATA["json"]["msg"] = "fortuna92";
$_DATA["json"]["format"] = "json";

$user_id = current_user();
if (!$user_id) {
    show_json_error("authentication error");
}

// Check parameters
foreach (array("msg","format") as $key) {
    if (!isset($_DATA["json"][$key]) || $_DATA["json"][$key] == "") {
        show_json_error("$key not found or void");
    }
}
$msg = $_DATA["json"]["msg"];
$format = $_DATA["json"]["format"];
if (!in_array($format, array("png","json"))) {
    show_json_error("unknown format $format");
}

$s = 6;
$m = 10;
$image = __qrcode($msg, $s, $m);
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
    "msg" => $msg,
    "image" => $data,
);
output_handler_json($result);
