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
 * Test system
 *
 * This test performs some tests to validate the correctness
 * of the system functions
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
final class test_system extends TestCase
{
    #[testdox('system functions')]
    /**
     * system test
     *
     * This test performs some tests to validate the correctness
     * of the system functions
     */
    public function test_system(): void
    {
        $array = check_system();
        $this->assertCount(0, $array);

        if (file_exists('data/nada')) {
            rmdir('data/nada');
        }
        $this->assertDirectoryDoesNotExist('data/nada');
        mkdir('data/nada', 0444);
        $this->assertDirectoryExists('data/nada');

        $array = check_directories();
        $this->assertCount(1, $array);
        $this->assertSame($array[0]['error'], 'data/nada not writable');
        $this->assertDirectoryExists('data/nada');

        $json = test_cli_helper('setup', [], '', '', '');
        $this->assertCount(3, $json);
        $this->assertArrayHasKey('system', $json);
        $this->assertCount(0, $json['system']['output']);
        $this->assertArrayHasKey('directories', $json);
        $this->assertArrayHasKey('error', $json['directories']['output']['0']);
        $this->assertCount(2, $json['directories']['output']['0']);
        $this->assertArrayHasKey('error', $json['directories']['output']['0']);
        $this->assertArrayHasKey('details', $json['directories']['output']['0']);
        $this->assertSame($json['directories']['output']['0']['error'], 'data/nada not writable');
        $this->assertArrayHasKey('composer', $json);
        $this->assertCount(0, $json['composer']['output']);

        $this->assertDirectoryExists('data/nada');
        rmdir('data/nada');
        $this->assertDirectoryDoesNotExist('data/nada');

        $json = test_cli_helper('setup/apache https://127.0.0.1/saltos/code4/api', [], '', '', '');
        $this->assertCount(1, $json);
        $this->assertArrayHasKey('apache', $json);
        $this->assertCount(3, $json['apache']);
        $this->assertArrayHasKey('time', $json['apache']);
        $this->assertArrayHasKey('output', $json['apache']);
        $this->assertArrayHasKey('count', $json['apache']);
        $this->assertSame($json['apache']['output'], []);
        $this->assertSame($json['apache']['count'], 0);

        $json = test_cli_helper('setup/crm', [], '', '', '');
        $this->assertCount(1, $json);
        $this->assertArrayHasKey('setup', $json);
        $this->assertCount(2, $json['setup']);
        $this->assertArrayHasKey('time', $json['setup']);
        $this->assertArrayHasKey('total', $json['setup']);

        $json = test_cli_helper('setup/certs', [], '', '', '');
        $this->assertCount(1, $json);
        $this->assertArrayHasKey('setup', $json);
        $this->assertCount(2, $json['setup']);
        $this->assertArrayHasKey('time', $json['setup']);
        $this->assertArrayHasKey('total', $json['setup']);

        exec_check_system();
    }
}
