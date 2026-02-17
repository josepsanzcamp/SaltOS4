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

/**
 * This php comparison must be placed here to detect old versions that
 * breaks by the null coalescing operator found in other php scripts
 */
if (version_compare(PHP_VERSION, '7.1', '<')) {
    die('PHP 7.1 is required, currently installed version is ' . PHP_VERSION);
}

/**
 * This chdir allow to this script to locate all needed files. If you are
 * using a saltos instance with symbolics links, this resolves the correct
 * path for the instance and not for the real files
 */
chdir(dirname($_SERVER['SCRIPT_FILENAME']));

/**
 * We include all core files, note that the last file (zindex.php) launches
 * the old index.php code, this is separated to simplify the code structure
 * and prevent errors with old php versions that not supports the null
 * coalescing operator
 */
foreach (glob('php/autoload/*.php') as $file) {
    require $file;
}

// You never must to see this error, some wrong thing was occurred in zindex
show_php_error(['phperror' => 'Internal error']);
