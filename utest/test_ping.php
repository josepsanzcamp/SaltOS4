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
 * Test ping
 *
 * This test performs some tests to validate the correctness
 * of the ping functions
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
final class test_ping extends TestCase
{
    #[testdox('ping functions')]
    /**
     * ping test
     *
     * This test performs some tests to validate the correctness
     * of the ping functions
     */
    public function test_ping(): void
    {
        $result = test_web_helper('ping', null, '', '');
        $this->assertSame('<script>close()</script>', $result);

        $response = __url_get_contents('https://127.0.0.1/saltos/code4/api/?/`ping');
        $this->assertSame('<script>close()</script>', $response['body']);

        $key = array_key_search('content-type', $response['headers']);
        $value = strtok($response['headers'][$key], ';');
        $this->assertSame('text/html', $value);

        $key = array_key_search('expires', $response['headers']);
        $value = $response['headers'][$key];
        $this->assertSame('-1', $value);

        $key = array_key_search('cache-control', $response['headers']);
        $value = $response['headers'][$key];
        $this->assertStringContainsString('no-store', $value);
        $this->assertStringContainsString('no-cache', $value);
        $this->assertStringContainsString('must-revalidate', $value);
        $this->assertStringContainsString('post-check=0', $value);
        $this->assertStringContainsString('pre-check=0', $value);
        $this->assertStringContainsString('no-transform', $value);

        $key = array_key_search('pragma', $response['headers']);
        $value = $response['headers'][$key];
        $this->assertSame('no-cache', $value);
    }
}
