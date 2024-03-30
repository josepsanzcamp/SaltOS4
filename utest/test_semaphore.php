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
 * Test semaphore
 *
 * This test performs some tests to validate the correctness
 * of the semaphore functions
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
require_once "lib/utestlib.php";

/**
 * Main class of this unit test
 */
final class test_semaphore extends TestCase
{
    #[testdox('semaphore functions')]
    /**
     * semaphore test
     *
     * This test performs some tests to validate the correctness
     * of the semaphore functions
     */
    public function test_semaphore(): void
    {
        $this->assertSame(__semaphore_usleep(1) < 100, true);
        $this->assertSame(semaphore_acquire(), true);
        $this->assertSame(semaphore_acquire(), false);
        $this->assertSame(semaphore_release(), true);
        $this->assertSame(semaphore_release(), false);
        $this->assertSame(semaphore_acquire(), true);
        $this->assertStringContainsString(".sem", semaphore_file());
        $this->assertSame(semaphore_shutdown(), true);

        $dir = "data/cache";
        chmod($dir, 0555);
        $this->assertSame(semaphore_acquire(), false);
        chmod($dir, 0777);

        $file = semaphore_file();
        chmod($file, 0444);
        $this->assertSame(semaphore_acquire(), false);
        chmod($file, 0666);

        $fd = @fopen($file, "a");
        $result = flock($fd, LOCK_EX | LOCK_NB);
        $this->assertSame(semaphore_acquire(null, 1), false);
        flock($fd, LOCK_UN);
        fclose($fd);
        $fd = null;

        test_external_exec("semaphore*.php", "phperror.log");
    }
}
