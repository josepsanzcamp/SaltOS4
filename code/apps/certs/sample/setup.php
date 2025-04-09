<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2025 by Josep Sanz Campderrós
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
 * Setup helper module
 *
 * This file contains useful functions related to the setup process
 */

/**
 * TODO
 *
 * TODO
 */
function __setup_helper_certs()
{
    require_once 'php/lib/control.php';
    require_once 'php/lib/log.php';
    require_once 'php/lib/version.php';
    require_once 'php/lib/indexing.php';
    require_once 'apps/certs/php/nssdb.php';
    $time1 = microtime(true);

    // Import certificates
    $total = 0;
    $exists = count(__nssdb_list());
    if (!$exists) {
        __nssdb_init();
        $files = glob('apps/certs/sample/certs/*.p12');
        foreach ($files as $file) {
            $output = __nssdb_add($file, '1234');
            if (implode('', $output) == 'pk12util: PKCS12 IMPORT SUCCESSFUL') {
                $total++;
            }
        }
    }

    $time2 = microtime(true);
    return [
        'setup' => [
            'time' => round($time2 - $time1, 6),
            'total' => $total,
        ],
    ];
}
