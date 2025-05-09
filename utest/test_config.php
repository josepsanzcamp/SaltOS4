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
