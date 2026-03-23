<?php

/**
 *  ____        _ _    ___  ____  _  _
 * / ___|  __ _| | |_ / _ \/ ___|| || |
 * \___ \ / _` | | __| | | \___ \| || |_
 *  ___) | (_| | | |_| |_| |___) |__   _|
 * |____/ \__,_|_|\__|\___/|____/   |_|
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
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
    if (basename($file) === 'zindex.php') {
        continue;
    }
    require $file;
}

init_timer();
init_random();

global $_CONFIG;
$_CONFIG = eval_attr(prepare_config_files(xmlfiles2array(detect_config_files('xml/config.xml'))));
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
    //~ if (file_get_contents("/tmp/mssql.stop") !== getmypid()) {
        //~ return;
    //~ }
    //~ echo "\033[0;31mSQL Server found and started by utest\033[0m\n";
    //~ echo "\033[0;33mStopping it ...\033[0m\n";
    //~ ob_passthru("sudo systemctl stop mssql-server.service");
    //~ unlink("/tmp/mssql.stop");
//~ });
