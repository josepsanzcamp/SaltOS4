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

// BEGIN INCLUDING ALL CORE FILES
foreach (glob("php/autoload/*.php") as $file) {
    require $file;
}

// SOME IMPORTANT ITEMS
program_handlers();
time_get_usage(true);
check_system();

// NORMAL OPERATION
$_CONFIG = xml2array("xml/config.xml");
$_CONFIG = eval_attr($_CONFIG);
eval_iniset(get_default("ini_set"));
eval_putenv(get_default("putenv"));

db_connect();
db_schema();
db_static();

// COLLECT ALL INPUT DATA
$data = array(
    //~ "headers" => getallheaders(),
    "input" => null2array(json_decode(file_get_contents('php://input'), true)),
    "rest" => array_diff(explode("/", get_server("QUERY_STRING")),array("")),
);
//~ output_handler(array(
    //~ "data" => json_encode($data),
    //~ "type" => "application/json",
    //~ "cache" => false
//~ ));

// TAKE DECISIONS
if (count($data["input"]) + count($data["rest"]) == 0) {
    output_handler(array(
        "data" => file_get_contents("htm/index.min.htm"),
        "type" => "text/html",
        "cache" => false
    ));
}
