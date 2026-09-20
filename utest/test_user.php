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
 * Test users
 *
 * This test performs some tests to validate the correctness
 * of the users functions
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
final class test_user extends TestCase
{
    #[testdox('authtoken action')]
    /**
     * Authtoken
     *
     * This function execute the authtoken rest request, and must to get the
     * json with the valid token to continue in the nexts unit tests
     */
    public function test_authtoken(): array
    {
        $json = test_web_helper('auth/login', [
            'user' => 'admin',
            'pass' => 'admin',
        ], '', '');
        $this->assertSame($json['status'], 'ok');
        $this->assertSame(count($json), 4);
        $this->assertArrayHasKey('token', $json);
        return $json;
    }

    #[Depends('test_authtoken')]
    #[testdox('users functions')]
    /**
     * users test
     *
     * This test performs some tests to validate the correctness
     * of the users functions
     */
    public function test_user(array $json): void
    {
        $token = $json['token'];
        $row = execute_query("SELECT * FROM tbl_users_tokens WHERE token='$token'");
        $this->assertArrayHasKey('token', $row);
        $this->assertArrayHasKey('remote_addr', $row);
        $this->assertArrayHasKey('user_agent', $row);

        set_data('server/token', $row['token']);
        $this->assertSame(get_data('server/token'), $row['token']);
        set_data('server/remote_addr', $row['remote_addr']);
        $this->assertSame(get_data('server/remote_addr'), $row['remote_addr']);
        set_data('server/user_agent', $row['user_agent']);
        $this->assertSame(get_data('server/user_agent'), $row['user_agent']);

        crontab_users();
        $this->assertTrue(true); // @phpstan-ignore method.alreadyNarrowedType

        $token = current_token();
        $this->assertSame($token, $row['id']);

        $user = current_user();
        $this->assertSame($user, 1);

        $group = current_group();
        $this->assertSame($group, 1);

        $groups = current_groups();
        $this->assertSame($groups, '1');

        set_data('server/token', '');
        $this->assertSame(get_data('server/token'), '');
        set_data('server/remote_addr', '');
        $this->assertSame(get_data('server/remote_addr'), '');
        set_data('server/user_agent', '');
        $this->assertSame(get_data('server/user_agent'), '');

        $token = current_token();
        $this->assertSame($token, 0);

        $user = current_user();
        $this->assertSame($user, 0);

        $group = current_group();
        $this->assertSame($group, 0);

        $groups = current_groups();
        $this->assertSame($groups, '0');
    }
}
