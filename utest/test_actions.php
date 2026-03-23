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
 * Test actions
 *
 * This test performs some tests to validate the correctness
 * of the actions functions
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
require_once 'php/lib/actions.php';

/**
 * Main class of this unit test
 */
final class test_actions extends TestCase
{
    #[testdox('actions functions')]
    /**
     * actions test
     *
     * This test performs some tests to validate the correctness
     * of the actions functions
     */
    public function test_actions(): void
    {
        $json = test_web_helper('auth/login', [
            'user' => 'admin',
            'pass' => 'admin',
        ], '', '');
        $this->assertSame($json['status'], 'ok');
        $this->assertSame(count($json), 4);
        $this->assertArrayHasKey('token', $json);

        $group_id = execute_query("SELECT id FROM tbl_groups WHERE code='admin'");
        $this->assertTrue(is_numeric($group_id));
        $this->assertTrue($group_id > 0);

        $json2 = test_web_helper("app/groups/delete/$group_id", '', $json['token'], '');
        $this->assertSame($json2['status'], 'ko');
        $this->assertSame(count($json2), 3);
        $this->assertArrayHasKey('text', $json2);
        $this->assertTrue(words_exists('data used by others apps', $json2['text']));
    }
}
