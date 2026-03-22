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

global $_CONFIG;
$_CONFIG = eval_attr(prepare_config_files(xmlfiles2array(detect_config_files('xml/config.xml'))));
eval_iniset(get_config('iniset'));
eval_putenv(get_config('putenv'));
eval_extras(get_config('extras'));

$cache = get_cache_file(__FILE__, '.tmp');
if (file_exists($cache)) {
    unlink($cache);
}
$size = intval(memory_get_free(true) / 3) + 1;
file_put_contents($cache, str_repeat('x', $size));
output_handler([
    'file' => $cache,
    'cache' => true,
]);
