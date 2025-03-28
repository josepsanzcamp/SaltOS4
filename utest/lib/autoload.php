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

// phpcs:disable PSR1.Files.SideEffects

/**
 * Autoload file for the unit tests
 *
 * This file contains the code that initialize the unit tests
 */

/**
 * Importing namespaces
 */
use PHPUnit\Framework\Assert;

/**
 * Main autoloader code
 *
 * This code emmulates the index.php by loading all autoload files excep
 * the zindex.php, initialize the timer and the random generator
 */

set_include_path(get_include_path() . ':' . getcwd() . '/' . 'utest');

chdir('code/api');
foreach (glob('php/autoload/*.php') as $file) {
    if (basename($file) == 'zindex.php') {
        continue;
    }
    require $file;
}

init_timer();
init_random();

global $_CONFIG;
$_CONFIG = eval_attr(xmlfiles2array(detect_config_files('xml/config.xml')));
ini_set('date.timezone', $_CONFIG['iniset']['date.timezone']);
db_connect();

$files = glob('data/logs/*');
if (count($files)) {
    echo "\033[0;31mLog files found: " . implode(', ', $files) . "\033[0m\n";
    echo "Push enter to continue or ctrl+c to break\n";
    readline();
    echo "\033[0;33mRemoving files ...\033[0m\n";
    foreach ($files as $file) {
        unlink($file);
    }
}

$files = glob('data/temp/pcov.out');
if (count($files)) {
    echo "\033[0;31mCoverage pipe found: " . implode(', ', $files) . "\033[0m\n";
    echo "Push enter to continue or ctrl+c to break\n";
    readline();
    echo "\033[0;33mRemoving files ...\033[0m\n";
    foreach ($files as $file) {
        unlink($file);
    }
}

//~ $mssql = intval(ob_passthru("ps uaxw | grep sqlservr | grep -v grep | wc -l"));
//~ if (!$mssql) {
    //~ echo "\033[0;31mSQL Server not found\033[0m\n";
    //~ echo "\033[0;33mStarting it ...\033[0m\n";
    //~ ob_passthru("sudo systemctl start mssql-server.service");
    //~ file_put_contents("/tmp/mssql.stop", getmypid());
//~ }

//~ register_shutdown_function(function () {
    //~ if (!file_exists("/tmp/mssql.stop")) {
        //~ return;
    //~ }
    //~ if (file_get_contents("/tmp/mssql.stop") != getmypid()) {
        //~ return;
    //~ }
    //~ echo "\033[0;31mSQL Server found and started by utest\033[0m\n";
    //~ echo "\033[0;33mStopping it ...\033[0m\n";
    //~ ob_passthru("sudo systemctl stop mssql-server.service");
    //~ unlink("/tmp/mssql.stop");
//~ });
