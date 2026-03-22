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

set_server('HTTP_ACCEPT_ENCODING', 'gzip');
output_handler([
    'file' => '../../utest/files/numbers.json',
    'cache' => true,
]);
