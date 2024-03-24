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
 * Test memory
 *
 * This test performs some tests to validate the correctness
 * of the memory functions
 */

/**
 * Importing namespaces
 */
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\DependsExternal;

/**
 * Main class of this unit test
 */
final class test_memory extends TestCase
{
    #[testdox('memory functions')]
    /**
     * memory test
     *
     * This test performs some tests to validate the correctness
     * of the memory functions
     */
    public function test_memory(): void
    {
        $this->assertSame(memory_get_free(false), 0);
        $this->assertSame(memory_get_free(true), INF);

        $this->assertSame(init_timer(), null);

        $this->assertSame(time_get_usage(false) < 1, true);
        $this->assertSame(time_get_usage(true) < 1, true);

        $this->assertSame(time_get_free(false) > 99, true);
        $this->assertSame(time_get_free(true) > 500, true);

        $this->assertSame(set_max_memory_limit(), null);
        $this->assertSame(set_max_execution_time(), null);

    }
}
