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
