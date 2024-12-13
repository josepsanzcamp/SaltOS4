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

// phpcs:disable PSR1.Files.SideEffects

/**
 * This php comparison must be placed here to detect old versions that
 * breaks by the null coalescing operator found in other php scripts
 */
if (version_compare(PHP_VERSION, '7.0', '<')) {
    die('PHP 7.0 is required, currently installed version is ' . PHP_VERSION);
}

/**
 * This chdir allow to execute this script from a command line and locate
 * all filese needed for the correct execution
 */
if (isset($argv) && defined('STDIN')) {
    chdir(__DIR__);
}

/**
 * We include all core files, note that the last file (zindex.php) launches
 * the old index.php code, this is separated to simplify the code structure
 * and prevent errors with old php versions that not supports the null
 * coalescing operator
 */
define('__ROOT__', __DIR__ . '/');
foreach (glob('php/autoload/*.php') as $file) {
    require __ROOT__ . $file;
}

// You never must to see this error, some wrong thing was occurred in zindex
show_php_error(['phperror' => 'Internal error']);
