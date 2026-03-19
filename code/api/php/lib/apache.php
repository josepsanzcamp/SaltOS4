<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
 */

declare(strict_types=1);

/**
 * TODO
 */

/**
 * TODO
 */
function check_apache($url)
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
    if (isset($response['headers']['Server']) && $response['headers']['Server'] !== 'Apache') {
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
        "$url/data/",
        "$url/data/files/",
        "$url/data/files/config.xml",
        "$url/data/files/saltos.sqlite",
        "$url/lib/",
        "$url/lib/tcpdf/vendor/tecnickcom/tcpdf/examples/",
        "$url/lib/tcpdf/vendor/tecnickcom/tcpdf/examples/index.php",
        "$url/lib/browscap/update.php",
        "$url/xml/",
        "$url/xml/config.xml",
    ];
    foreach ($urls as $temp) {
        $response = __url_get_contents($temp);
        $forbidden1 = words_exists('403 Forbidden', $response['body']);
        $forbidden2 = words_exists('403 Forbidden', array_keys($response['headers'])[0]);
        if (!$forbidden1 || !$forbidden2) {
            $result[] = [
                'warning' => "Access allowed to $temp",
                'details' => 'Enable the .htaccess files in your apache configuration',
            ];
        }
    }

    return $result;
}
