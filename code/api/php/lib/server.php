<?php

/**
 *  ____        _ _    ___  ____  _  _
 * / ___|  __ _| | |_ / _ \/ ___|| || |
 * \___ \ / _` | | __| | | \___ \| || |_
 *  ___) | (_| | | |_| |_| |___) |__   _|
 * |____/ \__,_|_|\__|\___/|____/   |_|
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
 */

declare(strict_types=1);

/**
 * Server helper module
 *
 * This file contains functions to check that the web server serving
 * the API is properly configured
 */

/**
 * Check server configuration
 *
 * This function requests the given URL to verify that the API is reachable and that
 * the web server/php configuration follows the security recommendations (exposed headers,
 * forbidden paths, Authorization Bearer header support, ...), returning an array of
 * errors and warnings found.
 */
function check_server($url)
{
    $result = [];

    $response = __url_get_contents("$url/");
    if (isset($response['error'])) {
        $result[] = [
            'error' => $response['error'],
            'details' => "Check that $url is a valid access to the API",
        ];
        return $result;
    }

    $first = array_key_first($response['headers']);
    if (!words_exists('http 200 ok', $first)) {
        $result[] = [
            'error' => $first,
            'details' => "Check that $url is a valid access to the API",
        ];
        return $result;
    }

    if (!isset($response['headers']['X-About']) || !words_exists('saltos', $response['headers']['X-About'])) {
        $result[] = [
            'error' => 'X-About header not found',
            'details' => "Check that $url is a valid access to the API",
        ];
        return $result;
    }

    // expose_php = Off
    if (isset($response['headers']['X-Powered-By'])) {
        $result[] = [
            'warning' => "X-Powered-By {$response['headers']['X-Powered-By']} header found",
            'details' => 'Set expose_php = Off in your php.ini configuration',
        ];
    }

    // ServerSignature Off
    // ServerTokens Prod
    if (isset($response['headers']['Server']) && str_starts_with($response['headers']['Server'], 'Apache/')) {
        $result[] = [
            'warning' => "Server {$response['headers']['Server']} header found",
            'details' => 'Set ServerSignature = Off and ServerTokens = Prod in your apache configuration',
        ];
    }

    // token part
    $token = get_unique_token();
    $response = __url_get_contents("$url/?/auth/test", [
        'headers' => ['Authorization' => "Bearer $token"],
    ]);
    $array = json_decode($response['body'], true);
    if (!is_array($array) || !isset($array['token']) || $array['token'] !== $token) {
        $result[] = [
            'error' => 'Authorization Bearer header not found',
            'details' => 'Check web server or proxy configuration for Authorization header support',
        ];
        return $result;
    }

    // forbidden part
    $urls = [
        "$url/apps/",
        "$url/apps/common/manifest.yaml",
        "$url/apps/crm/sample/",
        "$url/data/",
        "$url/data/cache/",
        "$url/data/cron/",
        "$url/data/files/",
        "$url/data/files/config.xml",
        "$url/data/files/dbstats.sqlite",
        "$url/data/files/saltos.sqlite",
        "$url/data/inbox/",
        "$url/data/logs/",
        "$url/data/logs/phperror.log",
        "$url/data/logs/saltos.log",
        "$url/data/outbox/",
        "$url/data/temp/",
        "$url/data/trash/",
        "$url/data/upload/",
        "$url/lib/",
        "$url/lib/tc-lib-pdf/import-atkinson.php",
        "$url/lib/tcpdf/vendor/tecnickcom/tcpdf/tcpdf.php",
        "$url/php/",
        "$url/php/action/setup.php",
        "$url/xml/",
        "$url/xml/config.xml",
    ];
    foreach ($urls as $temp) {
        $response = __url_get_contents($temp);
        if ($response['code'] !== 403) {
            $result[] = [
                'warning' => "Access allowed to $temp",
                'details' => 'Deny the access to this path in your web server configuration',
            ];
        }
    }

    return $result;
}
