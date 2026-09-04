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
 * Test about
 *
 * This test performs some tests to validate the correctness
 * of the about feature
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
final class test_about extends TestCase
{
    #[testdox('about functions')]
    /**
     * about test
     *
     * This test performs some tests to validate the correctness
     * of the about feature
     */
    public function test_about(): void
    {
        $json = test_web_helper('about', [], '', '');
        $this->assertArrayHasKey('about', $json);
        $this->assertArrayHasKey('copyright', $json);
        $this->assertSame($json['copyright'], 'Copyright (c) 2007-2026 Josep Sanz Campderrós');
        $this->assertArrayHasKey('license', $json);
        $this->assertArrayHasKey('license_id', $json);
        $this->assertSame($json['license_id'], 'MIT');
        $this->assertArrayHasKey('header', $json);
        $this->assertIsArray($json['header']);
        $this->assertGreaterThan(0, count($json['header']));
        $this->assertArrayHasKey('libraries', $json);
        $this->assertArrayHasKey('api', $json['libraries']);
        $this->assertArrayHasKey('stats', $json);
        $this->assertArrayHasKey('total', $json['stats']);
        $this->assertGreaterThan(0, $json['stats']['total']);
        $this->assertArrayHasKey('legal', $json);
        $this->assertStringContainsString('MIT License', implode(' ', $json['legal']));

        $json = test_web_helper('about/copyright', [], '', '');
        $this->assertSame(json_decode($json), 'Copyright (c) 2007-2026 Josep Sanz Campderrós');

        $json = test_web_helper('about/stats/total', [], '', '');
        $this->assertGreaterThan(0, json_decode($json));
    }
}
