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

// phpcs:disable PSR1.Classes.ClassDeclaration
// phpcs:disable Squiz.Classes.ValidClassName
// phpcs:disable PSR1.Methods.CamelCapsMethodName
// phpcs:disable PSR1.Files.SideEffects

/**
 * Test config
 *
 * This test performs some tests to validate the correctness
 * of the config functions
 */

/**
 * Importing namespaces
 */
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Depends;

/**
 * Loading helper function
 *
 * This file contains the needed function used by the unit tests
 */
require_once 'lib/utestlib.php';

/**
 * Main class of this unit test
 */
final class test_config extends TestCase
{
    #[testdox('config functions')]
    /**
     * config test
     *
     * This test performs some tests to validate the correctness
     * of the config functions
     */
    public function test_config(): void
    {
        db_disconnect();
        $this->assertSame(get_config('db', 0), null);
        db_connect();

        $this->assertSame(is_array(get_config('db')), true);
        $this->assertSame(get_config('db/user'), 'saltos');
        $this->assertSame(strlen(get_config('xml/dbschema.xml', 0)), 32);

        $uniqid = get_unique_id_md5();
        set_config('test', $uniqid, 1);
        $this->assertSame(get_config('test', 1), $uniqid);
        set_config('test', $uniqid . $uniqid, 1);
        $this->assertSame(get_config('test', 1), $uniqid . $uniqid);
        set_config('test', null, 1);
        $this->assertSame(get_config('test', 1), null);

        $uniqid = get_unique_id_md5();
        set_config('test', $uniqid, -1);
        $this->assertSame(get_config('test', -1), $uniqid);
        set_config('test', null, -1);
        $this->assertSame(get_config('test', -1), null);

        $this->assertSame(count(detect_config_files('xml/config.xml')), 1);
        $array = prepare_config_files(xmlfiles2array(detect_config_files('xml/config.xml')));
        $this->assertSame($array['db']['type'], 'pdo_mysql');

        $this->assertFileDoesNotExist('data/files/config.xml');
        file_put_contents('data/files/config.xml', '<root><db><type>pdo_sqlite</type></db></root>');
        $this->assertFileExists('data/files/config.xml');

        $this->assertSame(count(detect_config_files('xml/config.xml')) > 1, true);
        $array = prepare_config_files(xmlfiles2array(detect_config_files('xml/config.xml')));
        $this->assertSame($array['db']['type'], 'pdo_sqlite');

        $this->assertFileExists('data/files/config.xml');
        unlink('data/files/config.xml');
        $this->assertFileDoesNotExist('data/files/config.xml');

        test_external_exec('php/config1.php', 'phperror.log', 'key nada/nada/nada not found');
        test_external_exec('php/config2.php', 'phperror.log', 'key nada/nada/nada not found');

        $array = get_config_array('nada', '');
        $this->assertCount(0, $array);

        $array = get_config_array('xml/', '');
        $this->assertCount(2, $array);
    }
}
