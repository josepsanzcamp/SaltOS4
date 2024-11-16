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

foreach (glob('php/autoload/*.php') as $file) {
    if (basename($file) == 'zindex.php') {
        continue;
    }
    require $file;
}

pcov_start();
program_handlers();
init_timer();
init_random();

global $_CONFIG;
$_CONFIG = eval_attr(xmlfiles2array(detect_config_files('xml/config.xml')));
db_connect();

/**
 * Important notice about this part of the unit test:
 *
 * This execution tries to trigger the show_php_error placed after the putenv,
 * according to the documentation, putenv must return false in error cases, but
 * unfortunately php does not return false and catch a fatal error like this:
 *
 * PHP Fatal error:  Uncaught ValueError: putenv(): Argument #1 ($assignment)
 * must have a valid syntax in file.php:3
 *
 * For this reason, the show_php_error placed after the putenv that must to
 * be executed when putenv returns false never can be executed.
 */
set_config('putenv/', 'nada');
eval_putenv(get_config('putenv'));
