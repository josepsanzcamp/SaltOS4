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
 * Security helper module
 *
 * This file contain useful securiry helper functions
 */

/**
 * Get Browser Platform Device Type
 *
 * This function gets the browser, platform and device_type form the user_agent header
 */
function get_connection_detected($remote_addr, $user_agent = null)
{
    require_once 'php/lib/geoip.php';
    require_once 'php/lib/browser.php';
    $str = T('A connection has been detected from $geoip on a $browser.');
    $geoip = get_geoip_string($remote_addr);
    $browser = get_browser_string($user_agent);
    $str = eval("return \"$str\";");
    return $str;
}
