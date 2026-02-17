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
 * Browser helper module
 *
 * This file contain useful browser helper functions
 */

/**
 * Get Browser Platform Device Type
 *
 * This function gets the browser, platform and device_type form the user_agent header
 */
function get_browser_array($user_agent = null)
{
    require_once 'lib/browscap/vendor/autoload.php';
    $file = 'lib/browscap/vendor/browscap/browscap-php/resources/cache.sqlite';
    $db = new PDO("sqlite:$file");
    $adapter = new MatthiasMullie\Scrapbook\Adapters\SQLite($db);
    $cache = new MatthiasMullie\Scrapbook\Psr16\SimpleCache($adapter);
    $logger = new \Monolog\Logger('name');
    $bc = new \BrowscapPHP\Browscap($cache, $logger);
    $result = $bc->getBrowser($user_agent);
    return [
        'browser' => $result->browser,
        'platform' => $result->platform,
        'device_type' => $result->device_type,
    ];
}

/**
 * Get Browser Platform Device Type
 *
 * This function gets the browser, platform and device_type form the user_agent header
 */
function get_browser_string($user_agent = null)
{
    $str = T('$browser browser, $platform platform, and a $device_type device type');
    extract(get_browser_array($user_agent));
    $str = eval("return \"$str\";");
    return $str;
}
