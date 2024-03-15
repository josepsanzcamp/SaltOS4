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
 * CLI helper function
 *
 * This file contains the function used by the web unit tests to communicate with the
 * SaltOS app, using the two interfaces that SaltOS provides.
 */

/**
 * Test CLI helper
 *
 * This function allow to execute SaltOS using the CLI SAPI, to do it, the function
 * detects if data is provided, and executes the command and getting the output of
 * the execution. If data exists, then the contents are stored in a file and passed
 * the contents of the file to the stdin of the php process to emmulate the input
 * channel used by the apache server.
 *
 * As an example, this functions tries to execute the command using the follow formula:
 *
 * 1) php index.php $rest
 *
 * 2) cat /tmp/input | php index.php $rest
 *
 * In addition, the token field is used to define the TOKEN environment variable that
 * is used by SaltOS as variable to emmulate the TOKEN used by the apache for authenticate
 * the SaltOS app.
 *
 * @rest  => The rest request, like update/customers/3
 * @data  => The data used as json in the SaltOS app
 * @token => The token used if authentication is required
 */
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
