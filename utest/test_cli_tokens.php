<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
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
 * Test cli tokens (first part)
 *
 * This test performs some part of the actions related with the tokens suck
 * as authtoken and checktoken, using the cli sapi interface
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
final class test_cli_tokens extends TestCase
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
        $json = test_cli_helper('auth/login', [
            'user' => 'admin',
        ], '', '', '');
        $this->assertArrayHasKey('status', $json);
        $this->assertSame($json['status'], 'ko');
        $this->assertSame(count($json), 3);

        $json = test_cli_helper('auth/login', [
            'user' => 'nada',
            'pass' => 'admin',
        ], '', '', '');
        $this->assertArrayHasKey('status', $json);
        $this->assertSame($json['status'], 'ko');
        $this->assertSame(count($json), 3);

        $json = test_cli_helper('auth/login', [
            'user' => 'admin',
            'pass' => 'nada',
        ], '', '', '');
        $this->assertArrayHasKey('status', $json);
        $this->assertSame($json['status'], 'ko');
        $this->assertSame(count($json), 3);

        $user_id = execute_query("SELECT id FROM tbl_users WHERE login='admin'");

        $query = "UPDATE tbl_users_passwords SET user_id=-user_id WHERE user_id=$user_id";
        db_query($query);

        $json = test_cli_helper('auth/login', [
            'user' => 'admin',
            'pass' => 'admin',
        ], '', '', '');
        $this->assertArrayHasKey('status', $json);
        $this->assertSame($json['status'], 'ko');
        $this->assertSame(count($json), 3);

        $query = "UPDATE tbl_users_passwords SET user_id=-user_id WHERE user_id=-$user_id";
        db_query($query);

        $query = "UPDATE tbl_users_passwords SET password=MD5('admin') WHERE user_id=$user_id";
        db_query($query);

        $json = test_cli_helper('auth/login', [
            'user' => 'admin',
            'pass' => 'admin',
        ], '', '', '');
        $this->assertArrayHasKey('status', $json);
        $this->assertSame($json['status'], 'ok');
        $this->assertSame(count($json), 4);

        $json = test_cli_helper('auth/login', [
            'user' => 'admin',
            'pass' => 'admin',
        ], '', '', '');
        $this->assertSame($json['status'], 'ok');
        $this->assertSame(count($json), 4);
        $this->assertArrayHasKey('token', $json);
        return $json;
    }

    #[Depends('test_authtoken')]
    #[testdox('checktoken action')]
    /**
     * Checktoken
     *
     * This function execute the checktoken rest request, and must to get the
     * json with the ok about the valid token that you are trying to check
     */
    public function test_checktoken(array $json): array
    {
        $json = test_cli_helper('auth/check', '', $json['token'], '', '');
        $this->assertSame($json['status'], 'ok');
        $this->assertSame(count($json), 5);
        $this->assertArrayHasKey('token', $json);
        return $json;
    }

    #[Depends('test_authtoken')]
    #[testdox('deauthtoken action')]
    /**
     * Deauthtoken
     *
     * This function execute the deauthtoken rest request, and must to get the
     * json with the ok about the valid token that you are deauthenticate
     */
    public function test_deauthtoken(array $json): array
    {
        $json2 = test_cli_helper('auth/logout', '', $json['token'], '', '');
        $this->assertSame($json2['status'], 'ok');
        $this->assertSame(count($json2), 1);

        $json2 = test_cli_helper('auth/logout', '', $json['token'], '', '');
        $this->assertSame($json2['status'], 'ko');
        $this->assertSame(count($json2), 3);
        return $json;
    }

    #[Depends('test_deauthtoken')]
    #[testdox('checktoken ko action')]
    /**
     * Checktoken ko
     *
     * This function execute the checktoken rest request, and must to get the
     * json with the ko about the invalid token that you are trying to check
     */
    public function test_checktoken_ko(array $json): void
    {
        $json2 = test_cli_helper('auth/check', '', $json['token'], '', '');
        $this->assertSame($json2['status'], 'ko');
        $this->assertSame(count($json2), 3);
    }
}
