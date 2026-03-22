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
            if (implode('', $output) === 'pk12util: PKCS12 IMPORT SUCCESSFUL') {
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
