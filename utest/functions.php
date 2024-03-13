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

function test_cli_helper($rest, $data, $token): array
{
    if ($data) {
        file_put_contents("/tmp/input", json_encode($data));
        $response = ob_passthru("cat /tmp/input | TOKEN=$token php index.php $rest");
        unlink("/tmp/input");
    } else {
        $response = ob_passthru("TOKEN=$token php index.php $rest");
    }
    $json = json_decode($response, true);
    return $json;
}

function test_web_helper($rest, $data, $token): array
{
    if ($data) {
        $response = __url_get_contents("https://127.0.0.1/saltos/code4/api/index.php?$rest", [
            "body" => json_encode($data),
            "method" => "post",
            "headers" => [
                "Content-Type" => "application/json",
                "token" => $token,
            ],
        ]);
    } else {
        $response = __url_get_contents("https://127.0.0.1/saltos/code4/api/index.php?$rest", [
            "headers" => [
                "token" => $token,
            ],
        ]);
    }
    $json = json_decode($response["body"], true);
    return $json;
}
