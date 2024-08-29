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
        $response = __url_get_contents('https://127.0.0.1/saltos/code4/api/index.php?nada', [
            'method' => 'put',
        ]);
        test_pcov_stop(1);
        $json = json_decode($response['body'], true);
        $this->assertArrayHasKey('error', $json);

        test_pcov_start();
        $response = __url_get_contents('https://127.0.0.1/saltos/code4/api/index.php?nada', [
            'method' => 'get',
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
        test_pcov_stop(1);
        $json = json_decode($response['body'], true);
        $this->assertArrayHasKey('error', $json);

        test_pcov_start();
        $response = __url_get_contents('https://127.0.0.1/saltos/code4/api/index.php?nada', [
            'method' => 'post',
        ]);
        test_pcov_stop(1);
        $json = json_decode($response['body'], true);
        $this->assertArrayHasKey('error', $json);

        test_pcov_start();
        $response = __url_get_contents('https://127.0.0.1/saltos/code4/api/index.php?nada');
        test_pcov_stop(1);
        $json = json_decode($response['body'], true);
        $this->assertArrayHasKey('error', $json);
    }
}
