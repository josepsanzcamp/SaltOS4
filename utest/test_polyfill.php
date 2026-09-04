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
 * Test polyfill
 *
 * This test performs some tests to validate the correctness of the
 * api/php/autoload/polyfill.php polyfill against the native PHP
 * functions and posix extension it backfills: it calls the native
 * functions (available on this machine, since PHP 7.3+ and posix are
 * both present) and the polyfill's own __*_helper functions side by
 * side, and asserts each pair returns the same thing.
 *
 * Calling the *_helper functions directly (instead of the plain
 * names) is what lets this run on a machine that already has the
 * native functions: polyfill.php only defines the plain names when
 * they are missing, so on a machine that does have them, those names
 * are already the native functions and can't be used to reach the
 * polyfill's own code.
 */

/**
 * Importing namespaces
 */
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Depends;

/**
 * Main class of this unit test
 */
final class test_polyfill extends TestCase
{
    #[testdox('array_key_first and array_key_last functions')]
    /**
     * Array key first/last test
     *
     * This function performs some tests to validate the correctness
     * of the array_key_first and array_key_last polyfills by
     * comparing their output against the native PHP functions, over
     * a batch of sample arrays
     */
    public function test_array_key_first_last(): void
    {
        $this->assertTrue(function_exists('array_key_first'));
        $this->assertTrue(function_exists('array_key_last'));

        $arrays = [
            [],
            [1, 2, 3],
            ['a' => 1, 'b' => 2, 'c' => 3],
            [5 => 'x', 2 => 'y', 8 => 'z'],
            ['only' => 'one'],
            [0 => 'zero'],
            ['' => 'empty key', 'x' => 'y'],
        ];

        foreach ($arrays as $array) {
            $this->assertSame(array_key_first($array), __array_key_first_helper($array));
            $this->assertSame(array_key_last($array), __array_key_last_helper($array));
        }
    }

    #[testdox('posix_getuid function')]
    /**
     * Posix getuid test
     *
     * This function performs some tests to validate the correctness
     * of the posix_getuid polyfill by comparing its output against
     * the native posix extension function
     */
    public function test_posix_getuid(): void
    {
        $this->assertTrue(extension_loaded('posix'));

        $this->assertSame(posix_getuid(), __posix_getuid_helper());
    }
}
