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
 * Test error
 *
 * This test performs some tests to validate the correctness
 * of the error functions
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
final class test_error extends TestCase
{
    #[testdox('error functions')]
    /**
     * error function test
     *
     * This test performs some tests to validate the correctness
     * of the error functions
     */
    public function test_error(): void
    {
        $buffer = do_message_error([
            'dberror' => 'nada',
            'backtrace' => 'nada',
            'debug' => 'nada',
            'nada' => [],
            'nada2' => '',
            'code' => 'nada',
            'params' => ['nada'],
        ]);
        $this->assertSame(is_array($buffer), true);

        $this->assertSame(__get_code_from_trace(0), __FUNCTION__ . ':' . __LINE__);

        $this->assertSame(detect_recursion('test_error,test_error.php'), 2);

        test_external_exec('php/error1.php', 'phperror.log', 'test error');
        test_external_exec('php/error2.php', 'phperror.log', 'test error');
        test_external_exec('php/error3.php', 'phperror.log', 'test error');
        test_external_exec('php/error4.php', 'phperror.log', 'unknown type nada');
        test_external_exec('php/error5.php', 'phperror.log', 'allowed memory size exhausted tried allocate');
        test_external_exec('php/error6.php', 'deprecated.log', 'deprecated');
        test_external_exec('php/error7.php', 'phperror.log', 'test error');
    }
}
