<?php

/**
 *  ____        _ _    ___  ____    _  _    _
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / |
 * \___ \ / _` | | __| | | \___ \  | || |_ | |
 *  ___) | (_| | | |_| |_| |___) | |__   _|| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)_|
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
 * Test addlog
 *
 * This test performs some tests to validate the correctness
 * of the addlog functions
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
final class test_add extends TestCase
{
    #[testdox('addlog functions')]
    /**
     * addlog test
     *
     * This test performs some tests to validate the correctness
     * of the addlog functions
     */
    public function test_addlog(): void
    {
        $file = 'data/logs/saltos.log';
        $this->assertFileDoesNotExist($file);

        $json = test_web_helper('add/log', [], '', '');
        $this->assertArrayHasKey('error', $json);
        $this->assertFileDoesNotExist($file);

        $json = test_web_helper('add/log', [
            'msg' => 'hola mundo',
        ], '', '');
        $this->assertArrayHasKey('status', $json);
        $this->assertSame($json['status'], 'ok');
        $this->assertSame(count($json), 1);
        $this->assertFileExists($file);
        $this->assertTrue(words_exists('hola mundo', file_get_contents($file)));
        unlink($file);

        $file = 'data/logs/phperror.log';
        $this->assertFileDoesNotExist($file);
        $json = test_web_helper('add/nada', [], '', '');
        $this->assertArrayHasKey('error', $json);
        $this->assertFileExists($file);
        $this->assertTrue(words_exists('unknown action nada', file_get_contents($file)));
        unlink($file);
    }

    #[testdox('adderror functions')]
    /**
     * adderror test
     *
     * This test performs some tests to validate the correctness
     * of the adderror functions
     */
    public function test_adderror(): void
    {
        $file = 'data/logs/jserror.log';
        $this->assertFileDoesNotExist($file);

        $json = test_web_helper('add/error', [], '', '');
        $this->assertArrayHasKey('error', $json);
        $this->assertFileDoesNotExist($file);

        $json = test_web_helper('add/error', [
            'jserror' => 'hi jserror',
            'details' => 'hi details',
            'backtrace' => 'hi backtrace',
        ], '', '');
        $this->assertArrayHasKey('status', $json);
        $this->assertSame($json['status'], 'ok');
        $this->assertSame(count($json), 1);
        $this->assertFileExists($file);
        $this->assertTrue(words_exists('hi jserror hi details hi backtrace', file_get_contents($file)));
        unlink($file);

        $file = 'data/logs/phperror.log';
        $this->assertFileDoesNotExist($file);
        $json = test_web_helper('add/nada', [], '', '');
        $this->assertArrayHasKey('error', $json);
        $this->assertFileExists($file);
        $this->assertTrue(words_exists('unknown action nada', file_get_contents($file)));
        unlink($file);
    }
}
