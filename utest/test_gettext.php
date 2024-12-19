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

/**
 * Test gettext
 *
 * This test performs some tests to validate the correctness
 * of the gettext functions
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
final class test_gettext extends TestCase
{
    #[testdox('gettext functions')]
    /**
     * gettext test
     *
     * This test performs some tests to validate the correctness
     * of the gettext functions
     */
    public function test_gettext(): void
    {
        set_data('rest/0', 'app');
        set_data('rest/1', 'dashboard');
        set_data('server/lang', check_lang_format('en_US.UTF-8'));
        $this->assertSame(get_data('server/lang'), 'en_US');
        $this->assertSame(T('Close'), 'Close');
        set_data('server/lang', check_lang_format('es_ES.UTF-8'));
        $this->assertSame(get_data('server/lang'), 'es_ES');
        $this->assertSame(T('Close'), 'Cerrar');
        set_data('server/lang', check_lang_format('ca_ES'));
        $this->assertSame(get_data('server/lang'), 'ca_ES');
        $this->assertSame(T('Close'), 'Tancar');
        $this->assertSame(T('Nada'), 'Nada');
        $this->assertSame(T('Nada'), 'Nada');
        $this->assertIsArray(T());
        $this->assertSame(T('Language'), 'Idioma');

        $this->assertSame(check_lang_format(null), null);
        $this->assertSame(check_lang_format('AA'), null);
        $this->assertSame(check_lang_format('AA.asd'), null);
        $this->assertSame(check_lang_format('AAA-BB'), null);
        $this->assertSame(check_lang_format('AA-BBB'), null);
        $this->assertSame(check_lang_format('AA-BB'), 'aa_BB');
        $this->assertSame(check_lang_format('AA_BB'), 'aa_BB');
        $this->assertSame(check_lang_format('AA_BB.asd'), 'aa_BB');

        test_external_exec('php/gettext1.php', 'phperror.log', 'text is not string');
    }
}
