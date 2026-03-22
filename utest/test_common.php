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

// phpcs:disable PSR1.Classes.ClassDeclaration
// phpcs:disable Squiz.Classes.ValidClassName
// phpcs:disable PSR1.Methods.CamelCapsMethodName
// phpcs:disable PSR1.Files.SideEffects

/**
 * Test common
 *
 * This test performs some tests to validate the correctness
 * of the common functions
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
require_once 'php/lib/version.php';

/**
 * Main class of this unit test
 */
final class test_common extends TestCase
{
    #[testdox('common functions')]
    /**
     * common test
     *
     * This test performs some tests to validate the correctness
     * of the common functions
     */
    public function test_common(): void
    {
        $name = execute_query('SELECT name FROM app_customers WHERE id=100');

        $json = test_cli_helper('app/customers/update/100', ['name' => ''], '', '', 'admin');
        $this->assertSame($json['status'], 'ok');

        $json = test_cli_helper('app/customers/update/100', ['name' => $name], '', '', 'admin');
        $this->assertSame($json['status'], 'ok');

        $json = test_cli_helper('app/customers/view/version/100', [], '', '', 'admin');
        $this->assertIsArray($json);
        $this->assertStringContainsString('excel', sprintr($json));

        $json = test_cli_helper('app/customers/view/log/100', [], '', '', 'admin');
        $this->assertIsArray($json);
        $this->assertStringContainsString('excel', sprintr($json));

        $this->assertSame(del_version('customers', 100), 1);
        $this->assertSame(del_version('customers', 100), 1);
    }
}
