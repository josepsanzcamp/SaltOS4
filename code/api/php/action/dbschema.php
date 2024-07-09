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
 * DB Schema action
 *
 * This action executes the db_schema and db_static functions in the dbschema.php
 * library, the execution of this accion only is allowed from the command line
 */

if (get_data("server/request_method") != "CLI") {
    show_php_error(["phperror" => "Permission denied"]);
}

if (!semaphore_acquire("dbschema")) {
    show_php_error(["phperror" => "Could not acquire the semaphore"]);
}

db_connect();
require_once "php/lib/dbschema.php";
$dbschema_check = __dbschema_check();
$dbschema_hash = __dbschema_hash();
$dbstatic_check = __dbstatic_check();
$dbstatic_hash = __dbstatic_hash();
$time1 = microtime(true);
$output1 = db_schema();
$time2 = microtime(true);
$output2 = db_static();
$time3 = microtime(true);
semaphore_release("dbschema");
output_handler([
    "data" => json_encode([
        "db_schema" => array_merge([
            "time" => sprintf("%f", $time2 - $time1),
            "check" => $dbschema_check,
            "hash" => $dbschema_hash,
        ], $output1),
        "db_static" => array_merge([
            "time" => sprintf("%f", $time3 - $time2),
            "check" => $dbstatic_check,
            "hash" => $dbstatic_hash,
        ], $output2),
    ], JSON_PRETTY_PRINT) . "\n",
    "type" => "application/json",
    "cache" => false,
]);
