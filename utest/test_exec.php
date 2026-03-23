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
 * Test exec
 *
 * This test performs some tests to validate the correctness
 * of the exec functions
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
final class test_exec extends TestCase
{
    #[testdox('exec functions')]
    /**
     * exec test
     *
     * This test performs some tests to validate the correctness
     * of the exec functions
     */
    public function test_exec(): void
    {
        $this->assertSame(__exec_timeout('ls'), 'timeout 60 ls');

        $cache = get_cache_file('ls', '.out');
        if (file_exists($cache)) {
            unlink($cache);
        }
        $this->assertFileDoesNotExist($cache);
        $buffer = ob_passthru('ls', 60);
        $this->assertSame(strlen($buffer) > 0, true);

        is_disabled_function('add', 'passthru');
        $buffer = ob_passthru('ls');
        $this->assertSame(strlen($buffer) > 0, true);

        is_disabled_function('add', 'system');
        $buffer = ob_passthru('ls');
        $this->assertSame(strlen($buffer) > 0, true);

        is_disabled_function('add', 'exec');
        $buffer = ob_passthru('ls');
        $this->assertSame(strlen($buffer) > 0, true);

        is_disabled_function('add', 'shell_exec');
        $buffer = ob_passthru('ls');
        $this->assertSame(strlen($buffer) > 0, false);

        is_disabled_function('del', 'shell_exec');
        is_disabled_function('del', 'exec');
        is_disabled_function('del', 'system');
        is_disabled_function('del', 'passthru');
    }
}
