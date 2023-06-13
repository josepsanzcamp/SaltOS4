<?php

/*
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

// Begin including all core files
foreach (glob("php/autoload/*.php") as $file) {
    require $file;
}

// Some important items
program_handlers();
init_time_get();
check_system();

// Normal operation
$_CONFIG = eval_attr(xmlfile2array("xml/config.xml"));
eval_iniset(get_default("ini_set"));
eval_putenv(get_default("putenv"));

db_connect(); // TODO: THIS MUST TO BE DISABLE BY DEFAULT IN A FUTURE
db_schema(); // TODO: THIS MUST TO BE DISABLE BY DEFAULT IN A FUTURE
db_static(); // TODO: THIS MUST TO BE DISABLE BY DEFAULT IN A FUTURE

// Collect all input data
$data = array(
    //~ "headers" => getallheaders(),
    "json" => null2array(json_decode(file_get_contents('php://input'), true)),
    "rest" => array_diff(explode("/", get_server("QUERY_STRING")), array("")),
    "method" => strtoupper(get_server("REQUEST_METHOD")),
    "content-type" => strtolower(get_server("CONTENT_TYPE")),
    "token" => get_server("HTTP_TOKEN"),
);

//~ addlog(sprintr($data));
//~ addlog(sprintr($_SERVER));

// Check for an init browser request
if ($data["method"] == "GET" && count($data["rest"]) == 0) {
    output_handler(array(
        "data" => file_get_contents("htm/index.min.htm"),
        "type" => "text/html",
        "cache" => false
    ));
}

// Check for a GET REST action request
if ($data["method"] == "GET" && isset($data["rest"][0])) {
    $action = "php/action/" . encode_bad_chars($data["rest"][0]) . ".php";
    if (file_exists($action)) {
        require $action;
    }
}

// Check for a POST JSON action request
if ($data["method"] == "POST" && $data["content-type"] == "application/json" && isset($data["json"]["action"])) {
    $action = "php/action/" . encode_bad_chars($data["json"]["action"]) . ".php";
    if (file_exists($action)) {
        require $action;
    }
}

// Otherwise, we don't know what to do with this request
show_json_error("unknown request");
