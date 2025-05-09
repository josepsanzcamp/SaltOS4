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
 * Test math
 *
 * This test performs some tests to validate the correctness
 * of the math functions
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
require_once 'php/lib/math.php';

/**
 * Main class of this unit test
 */
final class test_math extends TestCase
{
    #[testdox('math functions')]
    /**
     * math test
     *
     * This test performs some tests to validate the correctness
     * of the math functions
     */
    public function test_math(): void
    {
        $this->assertSame(sign(3), 1);
        $this->assertSame(sign(0), 0);
        $this->assertSame(sign(-7), -1);

        $this->assertSame(is_prime(1), false);
        $this->assertSame(is_prime(4), false);
        $this->assertSame(is_prime(9), false);
        $this->assertSame(is_prime(25), false);
        $this->assertSame(is_prime(49), false);
        $this->assertSame(is_prime(2), true);
        $this->assertSame(is_prime(121), false);
        $this->assertSame(is_prime(67), true);
        $this->assertSame(is_prime(169), false);
        $this->assertSame(is_prime(149), true);
        $this->assertSame(is_prime(289), false);
        $this->assertSame(is_prime(197), true);
        $this->assertSame(is_prime(361), false);
        $this->assertSame(is_prime(331), true);
        $this->assertSame(is_prime(529), false);
        $this->assertSame(is_prime(401), true);
        $this->assertSame(is_prime(841), false);
        $this->assertSame(is_prime(577), true);
        $this->assertSame(is_prime(961), false);
        $this->assertSame(is_prime(907), true);
    }
}
