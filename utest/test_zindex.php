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
 * Test zindex
 *
 * This test performs some tests to validate the correctness
 * of the zindex functions
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
final class test_zindex extends TestCase
{
    #[testdox('zindex functions')]
    /**
     * zindex test
     *
     * This test performs some tests to validate the correctness
     * of the zindex functions
     */
    public function test_zindex(): void
    {
        $json = test_web_helper('', [], '', '');
        $this->assertArrayHasKey('error', $json);

        test_pcov_start();
        $response = __url_get_contents('https://127.0.0.1/saltos/code4/api/?/nada', [
            'method' => 'put',
        ]);
        test_pcov_stop(1);
        $json = json_decode($response['body'], true);
        $this->assertArrayHasKey('error', $json);

        test_pcov_start();
        $response = __url_get_contents('https://127.0.0.1/saltos/code4/api/?/nada', [
            'method' => 'get',
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
        test_pcov_stop(1);
        $json = json_decode($response['body'], true);
        $this->assertArrayHasKey('error', $json);

        test_pcov_start();
        $response = __url_get_contents('https://127.0.0.1/saltos/code4/api/?/nada', [
            'method' => 'post',
        ]);
        test_pcov_stop(1);
        $json = json_decode($response['body'], true);
        $this->assertArrayHasKey('error', $json);

        test_pcov_start();
        $response = __url_get_contents('https://127.0.0.1/saltos/code4/api/?/nada');
        test_pcov_stop(1);
        $json = json_decode($response['body'], true);
        $this->assertArrayHasKey('error', $json);
    }
}
