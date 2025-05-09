<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2025 by Josep Sanz Campderrós
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
 * Test authupdate
 *
 * This test performs some tests to validate the correctness
 * of the authupdate functions
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
require_once 'php/lib/auth.php';

/**
 * Main class of this unit test
 */
final class test_authupdate extends TestCase
{
    #[testdox('authupdate functions')]
    /**
     * authupdate test
     *
     * This test performs some tests to validate the correctness
     * of the authupdate functions
     */
    public function test_authupdate(): void
    {
        $json = test_web_helper('auth/update', '', '', '');
        $this->assertArrayHasKey('error', $json);

        $json2 = test_web_helper('auth/login', [
            'user' => 'admin',
            'pass' => 'admin',
        ], '', '');
        $this->assertSame($json2['status'], 'ok');
        $this->assertSame(count($json2), 4);
        $this->assertArrayHasKey('token', $json2);

        $json = test_web_helper('auth/update', [], $json2['token'], '');
        $this->assertArrayHasKey('error', $json);

        $json = test_web_helper('auth/update', [
            'oldpass' => 'nada',
            'newpass' => 'admin',
            'renewpass' => 'admin',
        ], '', '');
        $this->assertSame($json['status'], 'ko');

        $json = test_web_helper('auth/update', [
            'oldpass' => 'nada',
            'newpass' => 'admin',
            'renewpass' => 'admin',
        ], $json2['token'], '');
        $this->assertSame($json['status'], 'ko');

        // Check for internal error
        $file = 'data/logs/phperror.log';
        $this->assertFileDoesNotExist($file);

        $row = execute_query('SELECT * FROM tbl_users_passwords WHERE active=1 AND user_id=1');
        unset($row['id']);
        db_query(make_insert_query('tbl_users_passwords', $row));
        $id = execute_query('SELECT MAX(id) FROM tbl_users_passwords');

        $json = test_web_helper('auth/update', [
            'oldpass' => 'nada',
            'newpass' => 'admin',
            'renewpass' => 'admin',
        ], $json2['token'], '');
        $this->assertArrayHasKey('error', $json);

        db_query("DELETE FROM tbl_users_passwords WHERE id='$id'");

        $this->assertFileExists($file);
        $this->assertTrue(words_exists('internal error', file_get_contents($file)));
        unlink($file);

        // Continue with the other checks
        $json = test_web_helper('auth/update', [
            'oldpass' => 'admin',
            'newpass' => 'admin',
            'renewpass' => 'nada',
        ], $json2['token'], '');
        $this->assertSame($json['status'], 'ko');

        $json = test_web_helper('auth/update', [
            'oldpass' => 'admin',
            'newpass' => 'admin',
            'renewpass' => 'admin',
        ], $json2['token'], '');
        $this->assertSame($json['status'], 'ko');

        $json = test_web_helper('auth/update', [
            'oldpass' => 'admin',
            'newpass' => 'asd123ASD',
            'renewpass' => 'asd123ASD',
        ], $json2['token'], '');
        $this->assertArrayHasKey('status', $json);
        $this->assertSame($json['status'], 'ok');
        $this->assertSame(count($json), 3);

        $json = test_web_helper('auth/update', [
            'oldpass' => 'asd123ASD',
            'newpass' => 'asd123ASD',
            'renewpass' => 'asd123ASD',
        ], $json2['token'], '');
        $this->assertSame($json['status'], 'ko');

        $user_id = execute_query("SELECT id FROM tbl_users WHERE login='admin'");

        $query = "UPDATE tbl_users_passwords SET password=MD5('admin') WHERE user_id=$user_id";
        db_query($query);

        $json2 = test_web_helper('auth/login', [
            'user' => 'admin',
            'pass' => 'admin',
        ], '', '');
        $this->assertSame($json2['status'], 'ok');
        $this->assertSame(count($json2), 4);
        $this->assertArrayHasKey('token', $json2);

        $this->assertFalse(oldpass_check(0, ''));

        $file = 'data/logs/phperror.log';
        $this->assertFileDoesNotExist($file);
        $json = test_web_helper('auth/nada', [], '', '');
        $this->assertArrayHasKey('error', $json);
        $this->assertFileExists($file);
        $this->assertTrue(words_exists('unknown action nada', file_get_contents($file)));
        unlink($file);
    }
}
