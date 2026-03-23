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
 * Test ascii
 *
 * This test performs some tests to validate the correctness
 * of the ascii functions
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
require_once 'php/lib/ascii.php';

/**
 * Main class of this unit test
 */
final class test_ascii extends TestCase
{
    #[testdox('ascii functions')]
    /**
     * ascii test
     *
     * This test performs some tests to validate the correctness
     * of the ascii functions
     */
    public function test_ascii(): void
    {
        $array = [
            'rows' => 'ASD',
        ];
        $buffer = __ascii_make_table_ascii($array);
        $this->assertTrue(strlen($buffer) > 0);

        $array = [
            'rows' => [],
        ];
        $buffer = __ascii_make_table_ascii($array);
        $this->assertTrue(strlen($buffer) > 0);

        $array = [
            'rows' => [
                ['A', 'B', 'C', 'D'],
                ['1', '2', '3%', '4€'],
                ['5', '6', '7%', '8€'],
                ['9', 'a', 'b%', 'c€'],
                ['d', 'e', 'f%', 'g€'],
            ],
            'head' => true,
        ];
        $buffer = __ascii_make_table_ascii($array);
        $this->assertTrue(strlen($buffer) > 0);
    }
}
