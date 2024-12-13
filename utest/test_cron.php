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
require_once __ROOT__ . 'php/lib/cron.php';

/**
 * Main class of this unit test
 */
final class test_cron extends TestCase
{
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
    }

    /**
     * TODO
     *
     * TODO
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
}
