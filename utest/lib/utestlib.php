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
 * Unit test helper functions
 *
 * This file contains the functions used by the web and cli unit tests
 * to get the coverage, too contains the pcov and mime helpers.
 */

/**
 * Importing namespaces
 */
use PHPUnit\SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\SebastianBergmann\CodeCoverage\Test\TestStatus\TestStatus;

/**
 * Test PCOV start helper
 *
 * This function puts the flag needed to enable the pcov feature, to do it,
 * the function creates a void file and puts permissions to allow that other
 * procs can write inside of it
 *
 */
function test_pcov_start(): void
{
    touch("coverage.cov");
    chmod("coverage.cov", 0666);
}

/**
 * Test PCOV stop helper
 *
 * This function gets the contents of the coverage file and removes it, too,
 * it does all needed things to append the collected coverage to the current
 * unit test coverage instance
 */
function test_pcov_stop(): void
{
    for (;;) {
        $buffer = file_get_contents("coverage.cov");
        if (substr($buffer, -1, 1) == "}") {
            break;
        }
        usleep(1);
    }
    unlink("coverage.cov");
    $collected = unserialize($buffer);

    $coverage = RawCodeCoverageData::fromXdebugWithoutPathCoverage($collected);
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    $method = "unknown::unknown";
    if (isset($backtrace[2]) && isset($backtrace[2]["function"]) && isset($backtrace[2]["class"])) {
        $method = $backtrace[2]["class"] . "::" . $backtrace[2]["function"];
    }
    CodeCoverage::instance()->codeCoverage()->append(
        $coverage,
        $method,
        true,
        TestStatus::unknown(),
    );
}

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
function test_web_helper($rest, $data, $token)
{
    test_pcov_start();
    if ($data) {
        $response = __url_get_contents("https://127.0.0.1/saltos/code4/api/index.php?$rest", [
            "body" => json_encode($data),
            "method" => "post",
            "headers" => [
                "Content-Type" => "application/json",
                "Token" => $token,
            ],
        ]);
    } else {
        $response = __url_get_contents("https://127.0.0.1/saltos/code4/api/index.php?$rest", [
            "headers" => [
                "Token" => $token,
            ],
        ]);
    }
    test_pcov_stop();
    $json = $response["body"];
    if (substr($json, 0, 1) == "{") {
        $json = json_decode($json, true);
    }
    return $json;
}

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
function test_cli_helper($rest, $data, $token)
{
    test_pcov_start();
    if ($data) {
        file_put_contents("/tmp/input", json_encode($data));
        $response = ob_passthru("cat /tmp/input | TOKEN=$token php index.php $rest");
        unlink("/tmp/input");
    } else {
        $response = ob_passthru("TOKEN=$token php index.php $rest");
    }
    test_pcov_stop();
    $json = $response;
    if (substr($json, 0, 1) == "{") {
        $json = json_decode($json, true);
    }
    return $json;
}

/**
 * Get Mime helper
 *
 * This function returns the content type of the contents of the buffer
 *
 * @buffer => the contents that you want to check
 */
function get_mime($buffer): string
{
    $file = get_temp_file();
    file_put_contents($file, $buffer);
    $mime = trim(ob_passthru("file -b $file"));
    unlink($file);
    return $mime;
}
