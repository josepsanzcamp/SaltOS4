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

// phpcs:disable PSR1.Classes.ClassDeclaration
// phpcs:disable Squiz.Classes.ValidClassName
// phpcs:disable PSR1.Methods.CamelCapsMethodName
// phpcs:disable PSR1.Files.SideEffects

/**
 * Test password
 *
 * This test performs some tests to validate the correctness
 * of the password functions
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
require_once 'php/lib/password.php';

/**
 * Main class of this unit test
 */
final class test_password extends TestCase
{
    #[testdox('password functions')]
    /**
     * password test
     *
     * This test performs some tests to validate the correctness
     * of the password functions
     */
    public function test_password(): void
    {
        $this->assertSame(password_strength('admin'), 0);
        $this->assertSame(password_strength('Admin.123'), 50);
        $this->assertSame(password_strength('#Admin.123.'), 75);
        $this->assertSame(password_strength('#Admin.123..'), 100);
    }
}
