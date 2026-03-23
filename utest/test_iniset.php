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
// phpcs:disable Generic.Files.LineLength

/**
 * Test iniset
 *
 * This test performs some tests to validate the correctness
 * of the iniset functions
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
final class test_iniset extends TestCase
{
    #[testdox('iniset functions')]
    /**
     * iniset test
     *
     * This test performs some tests to validate the correctness
     * of the iniset functions
     */
    public function test_iniset(): void
    {
        set_config('iniset/display_errors', 'On');

        $this->assertSame(ini_get('memory_limit'), '-1');
        $this->assertSame(ini_get('max_execution_time'), '0');
        eval_iniset(get_config('iniset'));
        $this->assertSame(ini_get('memory_limit'), '128M');
        $this->assertSame(ini_get('max_execution_time'), '600');

        ini_set('memory_limit', -1);
        $this->assertSame(ini_get('memory_limit'), '-1');
        ini_set('max_execution_time', 0);
        $this->assertSame(ini_get('max_execution_time'), '0');

        $this->assertSame(getenv('LANG'), 'en_US.UTF-8');
        eval_putenv(get_config('putenv'));
        $this->assertSame(getenv('LANG'), 'es_ES.UTF-8');

        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertSame(mb_internal_encoding('ISO-8859-1'), true);
        $this->assertSame(mb_internal_encoding(), 'ISO-8859-1');
        eval_extras(get_config('extras'));
        $this->assertSame(mb_internal_encoding(), 'UTF-8');

        test_external_exec('php/iniset1.php', 'phperror.log', 'ini_set fails to set nada from to nada');
        test_external_exec('php/iniset2.php', 'phperror.log', 'putenv fails to set from to nada');
        test_external_exec('php/iniset3.php', 'phperror.log', 'is_array fails to set nada');
    }
}
