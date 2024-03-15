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
 * WEB helper function
 *
 * This file contains the function used by the web unit tests to communicate with the
 * SaltOS app, using the two interfaces that SaltOS provides.
 */

/**
 * Test WEB helper
 *
 * This function performs the action defined by the rest verb sendind the data if it is
 * provided and using the token for authentication actions.
 *
 * As you can see in the code, the function detects if data is provided and send the request
 * using GET or POST, in addition, an application/json content-type header is send when POST
 * is used.
 *
 * The token is sent using the TOKEN header to be used in the authentication process.
 *
 * @rest  => The rest request, like update/customers/3
 * @data  => The data used as json in the SaltOS app
 * @token => The token used if authentication is required
 */
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
