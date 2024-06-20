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
        set_data("rest/0", "app");
        set_data("rest/1", "dashboard");
        set_data("server/lang", check_lang_format("en_US.UTF-8"));
        $this->assertSame(get_data("server/lang"), "en_US");
        $this->assertSame(T("Customers"), "Customers");
        set_data("server/lang", check_lang_format("es_ES.UTF-8"));
        $this->assertSame(get_data("server/lang"), "es_ES");
        $this->assertSame(T("Customers"), "Clientes");

        $this->assertSame(check_lang_format("AA"), "");
        $this->assertSame(check_lang_format("AA.asd"), "");
        $this->assertSame(check_lang_format("AAA-BB"), "");
        $this->assertSame(check_lang_format("AA-BBB"), "");
        $this->assertSame(check_lang_format("AA-BB"), "aa_BB");
        $this->assertSame(check_lang_format("AA_BB"), "aa_BB");
        $this->assertSame(check_lang_format("AA_BB.asd"), "aa_BB");
    }
}
