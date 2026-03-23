<?php

/**
 *  ____        _ _    ___  ____  _  _
 * / ___|  __ _| | |_ / _ \/ ___|| || |
 * \___ \ / _` | | __| | | \___ \| || |_
 *  ___) | (_| | | |_| |_| |___) |__   _|
 * |____/ \__,_|_|\__|\___/|____/   |_|
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
 * Test ping
 *
 * This test performs some tests to validate the correctness
 * of the ping feature
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
     * of the ping feature
     */
    public function test_ping(): void
    {
        $response = __url_get_contents('https://127.0.0.1/saltos/code4/html/ping.html');
        $this->assertSame('<script>close()</script>', $response['body']);

        $key = array_key_search('content-type', $response['headers']);
        $value = $response['headers'][$key];
        $this->assertSame('text/html', $value);

        $key = array_key_search('content-length', $response['headers']);
        $value = $response['headers'][$key];
        $this->assertSame('24', $value);
    }
}
