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
 * Test authrenew
 *
 * This test performs some tests to validate the correctness
 * of the authrenew functions
 */

/**
 * Importing namespaces
 */
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Loading helper function
 *
 * This file contains the needed function used by the unit tests
 */
require_once 'lib/utestlib.php';
require_once 'php/lib/auth.php';

/**
 * Main class of this unit test
 */
final class test_authrenew extends TestCase
{
    #[testdox('authrenew functions')]
    /**
     * authrenew test
     *
     * This test performs some tests to validate the correctness
     * of the authrenew functions
     */
    public function test_authrenew(): void
    {
        $json = test_web_helper('auth/renew', '', '', '');
        $this->assertArrayHasKey('status', $json);
        $this->assertSame($json['status'], 'ko');
        $this->assertSame(count($json), 3);

        $json = test_web_helper('auth/renew', [
            'user' => 'admin',
        ], '', '');
        $this->assertArrayHasKey('status', $json);
        $this->assertSame($json['status'], 'ko');
        $this->assertSame(count($json), 3);

        // Sanity check, admin must to work before forcing the expiration
        $json = test_web_helper('auth/login', [
            'user' => 'admin',
            'pass' => 'admin',
        ], '', '');
        $this->assertSame($json['status'], 'ok');

        // Force the expiration of the current active password
        $query = 'UPDATE tbl_users_passwords SET active = 0 WHERE user_id = 1 AND active = 1';
        db_query($query);

        // A wrong password must to continue being rejected as ko, not as expired
        $json = test_web_helper('auth/login', [
            'user' => 'admin',
            'pass' => 'nada',
        ], '', '');
        $this->assertArrayHasKey('status', $json);
        $this->assertSame($json['status'], 'ko');
        $this->assertArrayNotHasKey('expired', $json);
        $this->assertSame(count($json), 3);

        // The correct but expired password must to be reported as expired
        $json = test_web_helper('auth/login', [
            'user' => 'admin',
            'pass' => 'admin',
        ], '', '');
        $this->assertArrayHasKey('status', $json);
        $this->assertSame($json['status'], 'ko');
        $this->assertArrayHasKey('expired', $json);
        $this->assertTrue($json['expired']);
        $this->assertSame(count($json), 4);

        // Regression: an expired password that was never migrated from a legacy hash
        // (because the user never logged in again after it was set) must still to be
        // reported as expired, not as a plain wrong password
        $id = execute_query(
            'SELECT id FROM tbl_users_passwords WHERE user_id = 1 AND active = 0 ORDER BY id DESC LIMIT 1'
        );
        db_query('UPDATE tbl_users_passwords SET password = ? WHERE id = ?', [md5('admin'), $id]);

        $json = test_web_helper('auth/login', [
            'user' => 'admin',
            'pass' => 'admin',
        ], '', '');
        $this->assertArrayHasKey('status', $json);
        $this->assertSame($json['status'], 'ko');
        $this->assertArrayHasKey('expired', $json);
        $this->assertTrue($json['expired']);
        $this->assertSame(count($json), 4);

        // Renew with a wrong old password must to fail
        $json = test_web_helper('auth/renew', [
            'user' => 'admin',
            'oldpass' => 'nada',
            'newpass' => 'qwe789ZXC.',
            'renewpass' => 'qwe789ZXC.',
        ], '', '');
        $this->assertArrayHasKey('status', $json);
        $this->assertSame($json['status'], 'ko');
        $this->assertSame(count($json), 3);

        // Renew with mismatched new passwords must to fail
        $json = test_web_helper('auth/renew', [
            'user' => 'admin',
            'oldpass' => 'admin',
            'newpass' => 'qwe789ZXC.',
            'renewpass' => 'nada',
        ], '', '');
        $this->assertArrayHasKey('status', $json);
        $this->assertSame($json['status'], 'ko');
        $this->assertSame(count($json), 3);

        // Renew reusing the old (weak and already used) password must to fail
        $json = test_web_helper('auth/renew', [
            'user' => 'admin',
            'oldpass' => 'admin',
            'newpass' => 'admin',
            'renewpass' => 'admin',
        ], '', '');
        $this->assertArrayHasKey('status', $json);
        $this->assertSame($json['status'], 'ko');
        $this->assertSame(count($json), 3);

        // Renew for an unknown user must to fail
        $json = test_web_helper('auth/renew', [
            'user' => 'nada',
            'oldpass' => 'admin',
            'newpass' => 'qwe789ZXC.',
            'renewpass' => 'qwe789ZXC.',
        ], '', '');
        $this->assertArrayHasKey('status', $json);
        $this->assertSame($json['status'], 'ko');
        $this->assertSame(count($json), 3);

        // The correct renew must to succeed and to return a valid token
        $json = test_web_helper('auth/renew', [
            'user' => 'admin',
            'oldpass' => 'admin',
            'newpass' => 'qwe789ZXC.',
            'renewpass' => 'qwe789ZXC.',
        ], '', '');
        $this->assertArrayHasKey('status', $json);
        $this->assertSame($json['status'], 'ok');
        $this->assertSame(count($json), 4);
        $this->assertArrayHasKey('token', $json);

        // The old (expired) password must not to work anymore
        $json = test_web_helper('auth/login', [
            'user' => 'admin',
            'pass' => 'admin',
        ], '', '');
        $this->assertArrayHasKey('status', $json);
        $this->assertSame($json['status'], 'ko');
        $this->assertSame(count($json), 3);

        // The new password must to work right away, without any expiration
        $json = test_web_helper('auth/login', [
            'user' => 'admin',
            'pass' => 'qwe789ZXC.',
        ], '', '');
        $this->assertArrayHasKey('status', $json);
        $this->assertSame($json['status'], 'ok');
        $this->assertSame(count($json), 4);

        // Check for internal error
        $file = 'data/logs/phperror.log';
        $this->assertFileDoesNotExist($file);

        // Restore the admin password back to its original value, reusing the same
        // migration trick used by test_authupdate to leave the fixture untouched
        $hash = md5('admin');
        $query = 'UPDATE tbl_users_passwords SET password = ? WHERE user_id = 1 AND active = 1';
        db_query($query, [$hash]);

        $json = test_web_helper('auth/login', [
            'user' => 'admin',
            'pass' => 'admin',
        ], '', '');
        $this->assertSame($json['status'], 'ok');
        $this->assertSame(count($json), 4);
    }
}
