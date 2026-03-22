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
 * Test cron
 *
 * This test performs some tests to validate the correctness
 * of the cron functions
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
require_once 'php/lib/cron.php';

/**
 * Main class of this unit test
 */
final class test_cron extends TestCase
{
    /**
     * Wait cron
     *
     * This function waits 10 seconds until all cron processes are running
     */
    private function wait_cron(): void
    {
        $pids = glob('data/cron/*.pid');
        foreach ($pids as $key => $val) {
            $pids[$key] = unserialize(file_get_contents($val))['pid'];
        }

        for ($i = 0; $i < 10000; $i++) {
            foreach ($pids as $key => $val) {
                if (!posix_kill($val, 0)) {
                    unset($pids[$key]);
                }
            }
            if (!count($pids)) {
                break;
            }
            usleep(1000);
        }
        $this->assertCount(0, $pids);
    }

    #[testdox('cron function')]
    /**
     * cron
     *
     * This test performs some tests to validate the correctness
     * of the cron functions
     */
    public function test_cron(): void
    {
        $file = 'data/logs/phperror.log';
        $this->assertFileDoesNotExist($file);

        $json = test_web_helper('cron', [], '', '');
        $this->assertArrayHasKey('error', $json);
        $this->assertFileExists($file);
        $this->assertTrue(words_exists('permission denied', file_get_contents($file)));
        unlink($file);

        $json = test_cli_helper('cron', [], '', '', '');
        $this->assertCount(2, $json);
        $this->assertArrayHasKey('cron_gc', $json);
        $this->assertArrayHasKey('cron_exec', $json);
        $this->assertCount(2, $json['cron_gc']);
        $this->assertArrayHasKey('time', $json['cron_gc']);
        $this->assertArrayHasKey('total', $json['cron_gc']);
        $this->assertCount(2, $json['cron_exec']);
        $this->assertArrayHasKey('time', $json['cron_exec']);
        $this->assertArrayHasKey('total', $json['cron_exec']);

        $this->wait_cron();

        if (file_exists('apps/common/xml/cron.xml')) {
            unlink('apps/common/xml/cron.xml');
        }
        $this->assertFileDoesNotExist('apps/common/xml/cron.xml');
        copy('../../utest/files/cron.xml', 'apps/common/xml/cron.xml');
        $this->assertFileExists('apps/common/xml/cron.xml');

        $json = test_cli_helper('cron', [], '', '', '');
        $this->assertCount(2, $json);
        $this->assertArrayHasKey('cron_gc', $json);
        $this->assertArrayHasKey('cron_exec', $json);
        $this->assertCount(2, $json['cron_gc']);
        $this->assertArrayHasKey('time', $json['cron_gc']);
        $this->assertArrayHasKey('total', $json['cron_gc']);
        $this->assertCount(2, $json['cron_exec']);
        $this->assertArrayHasKey('time', $json['cron_exec']);
        $this->assertArrayHasKey('total', $json['cron_exec']);

        $this->wait_cron();

        $json = test_cli_helper('cron', [], '', '', '');
        $this->assertCount(2, $json);
        $this->assertArrayHasKey('cron_gc', $json);
        $this->assertArrayHasKey('cron_exec', $json);
        $this->assertCount(2, $json['cron_gc']);
        $this->assertArrayHasKey('time', $json['cron_gc']);
        $this->assertArrayHasKey('total', $json['cron_gc']);
        $this->assertCount(2, $json['cron_exec']);
        $this->assertArrayHasKey('time', $json['cron_exec']);
        $this->assertArrayHasKey('total', $json['cron_exec']);

        $this->wait_cron();

        $this->assertFileExists('apps/common/xml/cron.xml');
        unlink('apps/common/xml/cron.xml');
        $this->assertFileDoesNotExist('apps/common/xml/cron.xml');

        $dir = get_directory('dirs/crondir') ?? getcwd_protected() . '/data/cron/';
        $files = glob($dir . '*');
        $this->assertCount(9, $files);

        $dir = get_directory('dirs/crondir') ?? getcwd_protected() . '/data/cron/';
        $files = glob($dir . '*');
        foreach ($files as $file) {
            unlink($file);
        }

        $dir = get_directory('dirs/crondir') ?? getcwd_protected() . '/data/cron/';
        $files = glob($dir . '*');
        $this->assertCount(0, $files);

        $this->assertSame(__cron_users('a,b'), ['a', 'b']);

        $this->assertSame(__cron_compare('1,2', '0'), false);
        $this->assertSame(__cron_compare('1,2', '1'), true);
        $this->assertSame(__cron_compare('1,2', '2'), true);
        $this->assertSame(__cron_compare('1,2', '3'), false);

        $this->assertSame(__cron_compare('1-2', '0'), false);
        $this->assertSame(__cron_compare('1-2', '1'), true);
        $this->assertSame(__cron_compare('1-2', '2'), true);
        $this->assertSame(__cron_compare('1-2', '3'), false);
    }
}
