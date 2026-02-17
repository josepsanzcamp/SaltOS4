<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
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
require_once 'lib/utestlib.php';

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
        $this->assertStringContainsString('.sem', semaphore_file());
        $this->assertSame(semaphore_shutdown(), true);

        $dir = 'data/cache';
        chmod($dir, 0555);
        $this->assertSame(semaphore_acquire(), false);
        chmod($dir, 0777);

        $file = semaphore_file();
        chmod($file, 0444);
        $this->assertSame(semaphore_acquire(), false);
        chmod($file, 0666);

        $fd = @fopen($file, 'a');
        $result = flock($fd, LOCK_EX | LOCK_NB);
        $this->assertSame(semaphore_acquire(null, 1), false);
        flock($fd, LOCK_UN);
        fclose($fd);
        $fd = null;

        test_external_exec('php/semaphore1.php', 'phperror.log', 'internal error');

        // This part of the test is to cover the errors of the actions
        // when tries to acquire the semaphore

        foreach (['auth', 'setup', 'gc', 'cron', 'integrity', 'indexing'] as $action) {
            $file1 = semaphore_file($action);
            if (file_exists($file1)) {
                unlink($file1);
            }
            touch($file1);
            chmod($file1, 0444);
            $this->assertFileExists($file1);

            $file2 = 'data/logs/phperror.log';
            $this->assertFileDoesNotExist($file2);

            $json = test_cli_helper($action, [], '', '', '');
            $this->assertArrayHasKey('error', $json);
            $this->assertFileExists($file2);
            $this->assertTrue(words_exists('could not acquire the semaphore', file_get_contents($file2)));
            unlink($file2);

            unlink($file1);
        }
    }
}
