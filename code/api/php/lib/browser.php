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
 * Browser helper module
 *
 * This file contain useful browser helper functions
 */

/**
 * Get Browser Platform Device Type
 *
 * This function gets the browser, platform and device_type form the user_agent header
 */
function get_browser_platform_device_type($user_agent = null)
{
    require 'lib/browscap/vendor/autoload.php';
    $dir = get_directory('dirs/filesdir') ?? getcwd_protected() . '/data/files/';
    $file = "$dir/browscap.sqlite";
    $exists = file_exists($file);
    if (!$exists) {
        touch($file);
        chmod_protected($file, 0666);
    }
    $db = new PDO("sqlite:$file");
    $adapter = new MatthiasMullie\Scrapbook\Adapters\SQLite($db);
    $cache = new MatthiasMullie\Scrapbook\Psr16\SimpleCache($adapter);
    $logger = new \Monolog\Logger('name');
    if (!$exists) {
        $bc = new \BrowscapPHP\BrowscapUpdater($cache, $logger);
        $bc->update(BrowscapPHP\Helper\IniLoaderInterface::PHP_INI_LITE);
    }
    $bc = new \BrowscapPHP\Browscap($cache, $logger);
    $result = $bc->getBrowser($user_agent);
    return [
        'browser' => $result->browser,
        'platform' => $result->platform,
        'device_type' => $result->device_type,
    ];
}
