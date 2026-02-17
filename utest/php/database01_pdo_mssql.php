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
 *
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
 */

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects

foreach (glob('php/autoload/*.php') as $file) {
    if (basename($file) === 'zindex.php') {
        continue;
    }
    require $file;
}

pcov_start();
program_handlers();
init_timer();
init_random();

db_connect([
    'type' => 'pdo_mssql',
    'host' => '127.0.0.1',
    'port' => 'nada',
    'name' => 'master',
    'user' => 'sa',
    'pass' => 'asd123ASD',
]);
