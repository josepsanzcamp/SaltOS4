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

    // access part, each url with the code that the web server must return:
    // 404 for the contents that must not be published in the web directory,
    // 403 for the directories of the web that must not show its contents and
    // 200 for the public contents, that too can define the content type that
    // the web server must return, the last urls detect when all the code is
    // published instead of only the web directory, they must return 404 or
    // 403 if they are protected by the web server, the ../api/index.php url
    // only can be checked when the web directory is not the root of the
    // server, otherwise it is the same url that the public api
    $web = dirname($url);
    $root = trim(strval(parse_url($web, PHP_URL_PATH)), '/') === '';
    $items = [
        ["$url/apps/", 404],
        ["$url/apps/common/xml/manifest.yaml", 404],
        ["$url/apps/crm/sample/", 404],
        ["$url/data/", 404],
        ["$url/data/cache/", 404],
        ["$url/data/cron/", 404],
        ["$url/data/files/", 404],
        ["$url/data/files/config.xml", 404],
        ["$url/data/files/dbstats.sqlite", 404],
        ["$url/data/files/saltos.sqlite", 404],
        ["$url/data/inbox/", 404],
        ["$url/data/logs/", 404],
        ["$url/data/logs/phperror.log", 404],
        ["$url/data/logs/saltos.log", 404],
        ["$url/data/outbox/", 404],
        ["$url/data/temp/", 404],
        ["$url/data/trash/", 404],
        ["$url/data/upload/", 404],
        ["$url/lib/", 404],
        ["$url/lib/tc-lib-pdf/import-atkinson.php", 404],
        ["$url/lib/tcpdf/vendor/tecnickcom/tcpdf/tcpdf.php", 404],
        ["$url/php/", 404],
        ["$url/php/action/setup.php", 404],
        ["$url/xml/", 404],
        ["$url/xml/config.xml", 404],
        ["$web/apps/", 403],
        ["$web/apps/common/js/", 403],
        ["$web/apps/common/xml/manifest.yaml", 404],
        ["$web/img/", 403],
        ["$web/js/", 403],
        ["$web/lib/", 403],
        ["$web/lib/pdfjs/pdf.worker.min.mjs", 200, 'javascript'],
        ["$web/../api/index.php", $root ? 200 : [403, 404]],
        ["$web/../api/xml/config.xml", [403, 404]],
        ["$web/../apps/common/xml/manifest.yaml", [403, 404]],
        ["$web/../data/files/config.xml", [403, 404]],
        ["$web/../data/files/saltos.sqlite", [403, 404]],
        ["$web/../data/logs/", [403, 404]],
    ];
    foreach ($items as $item) {
        [$temp, $code, $type] = array_pad($item, 3, '');
        $response = __url_get_contents($temp);
        $key = array_key_search('content-type', $response['headers']);
        $value = $response['headers'][$key] ?? '';
        if (!in_array($response['code'], (array) $code, true)) {
            $code = implode(' or ', (array) $code);
            $result[] = [
                'warning' => "Code {$response['code']} found in $temp, expected $code",
                'details' => 'Check the access to this path in your web server configuration',
            ];
        } elseif (!str_contains($value, $type)) {
            $result[] = [
                'warning' => "Content-Type $value found in $temp, expected $type",
                'details' => 'Check the content type of this path in your web server configuration',
            ];
        }
    }

    return $result;
}
