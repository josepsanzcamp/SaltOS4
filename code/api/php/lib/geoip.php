<?php

/**
 *  ____        _ _    ___  ____    _  _    _
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / |
 * \___ \ / _` | | __| | | \___ \  | || |_ | |
 *  ___) | (_| | | |_| |_| |___) | |__   _|| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)_|
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
 */

declare(strict_types=1);

/**
 * GeoIP helper module
 *
 * This file contain useful geoip helper functions
 */

/**
 * Get GeoIP information
 *
 * This function gets the geoip information an returns it
 */
function get_geoip_array($remote_addr)
{
    $cache = get_cache_file($remote_addr, '.geoip');
    if (!file_exists($cache)) {
        require_once 'lib/phpgeoip/vendor/autoload.php';
        $gp = new Webklex\GeoIP\GeoIP();
        $data = $gp->get($remote_addr);
        file_put_contents($cache, serialize($data));
        chmod_protected($cache, 0666);
    } else {
        $data = unserialize(file_get_contents($cache));
    }
    $country_name = $data['location']['country']['name'] ?? '';
    //~ $country_code = $data['location']['country']['code'] ?? '';
    $region_name = $data['location']['region_name'] ?? '';
    //~ $region_code = $data['location']['region_code'] ?? '';
    $city = $data['location']['city'] ?? '';
    //~ $zip_code = $data['location']['zip_code'] ?? '';
    return [
        //~ 'city' => "$city ($zip_code)",
        //~ 'region' => "$region_name ($region_code)",
        //~ 'country' => "$country_name ($country_code)",
        'city' => $city,
        'region' => $region_name,
        'country' => $country_name,
        'ip' => $data['network']['ip'] ?? '',
        'isp' => $data['network']['as']['name'] ?? '',
    ];
}

/**
 * Get GeoIP information
 *
 * This function gets the geoip information an returns it
 */
function get_geoip_string($remote_addr)
{
    $str = T('$city, $region, $country using IP $ip and network of $isp');
    extract(get_geoip_array($remote_addr));
    $str = eval("return \"$str\";");
    return $str;
}
