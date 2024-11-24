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
 * Test push
 *
 * This test performs some tests to validate the correctness
 * of the push functions
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
require_once 'php/lib/push.php';

/**
 * Main class of this unit test
 */
final class test_push extends TestCase
{
    #[testdox('push function')]
    /**
     * push
     *
     * This test performs some tests to validate the correctness
     * of the push functions
     */
    public function test_push(): void
    {
        $file = 'data/logs/phperror.log';
        $this->assertFileDoesNotExist($file);

        $json = test_web_helper('push', [], '', '');
        $this->assertArrayHasKey('error', $json);
        $this->assertFileExists($file);
        $this->assertTrue(words_exists('unknown action', file_get_contents($file)));
        unlink($file);

        $json = test_web_helper('push/set', [], '', '');
        $this->assertArrayHasKey('error', $json);
        $this->assertFileExists($file);
        $this->assertTrue(words_exists('permission denied', file_get_contents($file)));
        unlink($file);

        $json = test_web_helper('push/get', [], '', '');
        $this->assertArrayHasKey('error', $json);
        $this->assertCount(2, $json['error']);
        $this->assertTrue(words_exists('permission denied', $json['error']['text']));

        set_data('server/user', 'admin');
        $timestamp = microtime(true) - 1e-3;
        push_insert('success', 'test message');
        $rows = push_select($timestamp);
        $this->assertCount(1, $rows);
        $this->assertCount(3, $rows[0]);
        $this->assertArrayHasKey('type', $rows[0]);
        $this->assertArrayHasKey('message', $rows[0]);
        $this->assertArrayHasKey('timestamp', $rows[0]);
        $this->assertSame('success', $rows[0]['type']);
        $this->assertSame('test message', $rows[0]['message']);

        $json = test_cli_helper("push/get/$timestamp", [], '', '', 'admin');
        $this->assertCount(1, $json);
        $this->assertCount(3, $json[0]);
        $this->assertArrayHasKey('type', $json[0]);
        $this->assertArrayHasKey('message', $json[0]);
        $this->assertArrayHasKey('timestamp', $json[0]);
        $this->assertSame('success', $json[0]['type']);
        $this->assertSame('test message', $json[0]['message']);

        $json = test_cli_helper('push/set/success/test\ message', [], '', '', 'admin');
        $this->assertCount(1, $json);
        $this->assertCount(3, $json[0]);
        $this->assertArrayHasKey('type', $json[0]);
        $this->assertArrayHasKey('message', $json[0]);
        $this->assertArrayHasKey('timestamp', $json[0]);
        $this->assertSame('success', $json[0]['type']);
        $this->assertSame('test message', $json[0]['message']);
    }
}
