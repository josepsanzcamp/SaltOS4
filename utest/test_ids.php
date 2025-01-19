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
 * Test IDs
 *
 * This test performs some tests to validate the correctness
 * of the check_ids feature
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
final class test_ids extends TestCase
{
    #[testdox('IDs functions')]
    /**
     * IDs test
     *
     * This test performs some tests to validate the correctness
     * of the check_ids feature
     */
    public function test_ids(): void
    {
        $this->assertSame(check_ids(null), '0');
        $this->assertSame(check_ids(true), '1');
        $this->assertSame(check_ids(false), '0');
        $this->assertSame(check_ids([]), '0');
        $this->assertSame(check_ids(''), '0');
        $this->assertSame(check_ids(), '0');
        $this->assertSame(check_ids('1,2,3', '2,1'), '1,2,3');
        $this->assertSame(check_ids('a,b,c', 'b,a'), '0');

        $this->assertSame(check_ids_array(null), []);
        $this->assertSame(check_ids_array(true), [1]);
        $this->assertSame(check_ids_array(false), []);
        $this->assertSame(check_ids_array([]), []);
        $this->assertSame(check_ids_array(''), []);
        $this->assertSame(check_ids_array(), []);
        $this->assertSame(check_ids_array('1,2,3', '2,1'), [1, 2, 3]);
        $this->assertSame(check_ids_array('a,b,c', 'b,a'), []);
    }
}
