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
 * Test getdata
 *
 * This test performs some tests to validate the correctness
 * of the getdata functions
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
final class test_getdata extends TestCase
{
    #[testdox('getdata functions')]
    /**
     * getdata test
     *
     * This test performs some tests to validate the correctness
     * of the getdata functions
     */
    public function test_getdata(): void
    {
        set_data("server/token", get_unique_token());
        $this->assertSame(is_array(get_data("server")), true);
        $this->assertSame(count(get_data("server")), 1);
        $this->assertSame(strlen(get_data("server/token")), 36);

        set_data("server/token", null);
        $this->assertSame(is_array(get_data("server")), true);
        $this->assertSame(count(get_data("server")), 0);
        $this->assertSame(get_data("server/token"), null);

        set_data("server/token", get_unique_token());
        set_data("server", null);
        $this->assertSame(get_data("server"), null);
        $this->assertSame(get_data("server/token"), null);

        set_data("token", get_unique_token());
        $this->assertSame(strlen(get_data("token")), 36);
        set_data("token", null);
        $this->assertSame(get_data("token"), null);
    }
}
