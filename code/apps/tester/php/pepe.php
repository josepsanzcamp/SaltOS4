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
 * TODO
 */

$lines = file("apps/tester/pepe.txt");
foreach ($lines as $index => $line) {
    $line = trim(substr($line, 25));
    $data = json_decode($line, true);
    $temp = [
        "#attr" => [],
        "value" => [],
    ];
    foreach ($data as $key => $val) {
        if (is_array($val)) {
            if (!count($val)) {
                unset($data[$key]);
                continue;
            }
            $temp["value"][$key] = $val;
        } else {
            if ($val == "") {
                unset($data[$key]);
                continue;
            }
            $temp["#attr"][$key] = $val;
        }
    }
    if (!count($temp["value"])) {
        $temp["value"] = "";
    }
    foreach (["datalist", "rows", "data", "header", "footer", "images"] as $x) {
        if (isset($temp["value"][$x])) {
            $temp["value"][$x] = [
                "#attr" => ["eval" => "true"],
                "value" => json_encode($temp["value"][$x]),
            ];
        }
    }
    if (isset($temp["value"]["value_old"])) {
        $temp["value"]["value_old"] = implode(", ", $temp["value"]["value_old"]);
    }
    //~ $temp["value"] = "";
    $type = $temp["#attr"]["type"];
    unset($temp["#attr"]["type"]);
    $temp = [$type => $temp];
    if ($index < 36) {
        $lines[$index] = __array2xml_write_nodes($temp, 0);
    } else {
        print_r($temp);
        file_put_contents("data/logs/pepe.txt", implode($lines));
        die();
    }
}
file_put_contents("data/logs/pepe.txt", implode($lines));
die();
