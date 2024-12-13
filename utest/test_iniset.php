<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2024 by Josep Sanz Campderrós
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
        set_config('extras/ini_set', ['display_errors', 'Off']);

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
        //~ eval_extras(get_config('extras'));
        //~ $this->assertSame(mb_internal_encoding(), 'UTF-8');

        test_external_exec('php/iniset1.php', 'phperror.log', 'ini_set fails to set nada from to nada');
        test_external_exec('php/iniset2.php', 'phperror.log', 'putenv(): argument assignment must have a valid syntax');
        test_external_exec('php/iniset5.php', 'phperror.log', 'is_array fails to set nada');
    }
}
