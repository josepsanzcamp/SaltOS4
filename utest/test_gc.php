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
 * Test gc
 *
 * This test performs some tests to validate the correctness
 * of the gc functions
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
final class test_gc extends TestCase
{
    #[testdox('gc functions')]
    /**
     * gc test
     *
     * This test performs some tests to validate the correctness
     * of the gc functions
     */
    public function test_gc(): void
    {
        $file = get_temp_file();
        $this->assertFileDoesNotExist($file);
        file_put_contents($file, "");
        $this->assertFileExists($file);

        $old = get_config("server/cachetimeout");
        set_config("server/cachetimeout", 0);
        $this->assertSame(get_config("server/cachetimeout"), 0);

        sleep(1); // the internally filemtime used have one second of resolution
        gc_exec();
        $this->assertFileDoesNotExist($file);

        set_config("server/cachetimeout", $old);
        $this->assertSame(get_config("server/cachetimeout"), $old);
    }
}
