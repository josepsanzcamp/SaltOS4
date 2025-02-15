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
 * Test log
 *
 * This test performs some tests to validate the correctness
 * of the log functions
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
final class test_log extends TestCase
{
    #[testdox('log functions')]
    /**
     * log test
     *
     * This test performs some tests to validate the correctness
     * of the log functions
     */
    public function test_log(): void
    {
        $file0 = 'data/logs/saltos.log';
        $this->assertFileDoesNotExist($file0);

        $file1 = 'data/logs/saltos.1.log.gz';
        $this->assertFileDoesNotExist($file1);

        $file2 = 'data/logs/saltos.2.log.gz';
        $this->assertFileDoesNotExist($file2);

        addlog(['hola']);
        $this->assertFileExists($file0);
        $this->assertSame(fileperms($file0) & 0777, 0666);

        addlog(wordwrap(str_repeat(random_bytes(1024), 1024 * 100)));
        $this->assertSame(filesize($file0) > 1024 * 1024 * 100, true);

        addlog(['hola']);
        $this->assertFileExists($file1);
        $this->assertSame(fileperms($file1) & 0777, 0666);

        addlog(wordwrap(str_repeat(random_bytes(1024), 1024 * 100)));
        $this->assertSame(filesize($file0) > 1024 * 1024 * 100, true);

        addlog(['hola']);
        $this->assertFileExists($file2);
        $this->assertSame(fileperms($file2) & 0777, 0666);

        $this->assertSame(checklog('hola', 'saltos.log'), true);
        $this->assertSame(checklog('nada', 'saltos.log'), false);

        unlink($file0);
        $this->assertFileDoesNotExist($file0);

        unlink($file1);
        $this->assertFileDoesNotExist($file1);

        unlink($file2);
        $this->assertFileDoesNotExist($file2);
    }
}
