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
        mkdir('data/nada');
        $this->assertDirectoryExists('data/nada');

        $array = check_directories();
        $this->assertCount(1, $array);
        $this->assertSame($array[0]['error'], 'data/nada not writable');
        $this->assertDirectoryExists('data/nada');

        $json = test_cli_helper('setup', [], '', '', '');
        $this->assertCount(2, $json);
        $this->assertArrayHasKey('system', $json);
        $this->assertCount(0, $json['system']['output']);
        $this->assertArrayHasKey('directories', $json);
        $this->assertArrayHasKey('error', $json['directories']['output']['0']);
        $this->assertCount(2, $json['directories']['output']['0']);
        $this->assertArrayHasKey('error', $json['directories']['output']['0']);
        $this->assertArrayHasKey('details', $json['directories']['output']['0']);
        $this->assertSame($json['directories']['output']['0']['error'], 'data/nada not writable');

        $this->assertDirectoryExists('data/nada');
        rmdir('data/nada');
        $this->assertDirectoryDoesNotExist('data/nada');

        exec_check_system();
    }
}
