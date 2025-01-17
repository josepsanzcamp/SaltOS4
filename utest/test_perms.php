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
 * Test perms
 *
 * This test performs some tests to validate the correctness
 * of the perms functions
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
final class test_perms extends TestCase
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
    #[testdox('perms functions')]
    /**
     * perms test
     *
     * This test performs some tests to validate the correctness
     * of the perms functions
     */
    public function test_perms(array $json): void
    {
        $this->assertSame(check_user('dashboard', 'menu'), true);
        $this->assertSame(check_user('customers', 'view'), false);
        $this->assertSame(check_sql('customers', 'view'), '1=0');
        $this->assertSame(check_app_perm_id('dashboard', 'menu'), true);
        $this->assertSame(check_app_perm_id('customers', 'view'), false);

        test_external_exec('php/perms1.php', 'phperror.log', 'app nada not found');
        test_external_exec('php/perms2.php', 'phperror.log', 'perm nada not found');
        test_external_exec('php/perms3.php', 'phperror.log', 'nada(nada) not found');

        $token = $json['token'];
        $row = execute_query("SELECT * FROM tbl_users_tokens WHERE token='$token'");
        file_put_contents('/tmp/phpunit.token', $row['token']);
        file_put_contents('/tmp/phpunit.remote_addr', $row['remote_addr']);
        file_put_contents('/tmp/phpunit.user_agent', $row['user_agent']);

        // remove all perms to force the internal error of check_user
        $rows = execute_query_array('SELECT * FROM tbl_apps_perms');
        db_query('TRUNCATE TABLE tbl_apps_perms');

        test_external_exec('php/perms5.php', 'phperror.log', 'internal error for');

        foreach ($rows as $row) {
            db_query(make_insert_query('tbl_apps_perms', $row));
        }

        // remove some perms to force the last return of the check_sql
        $perms_ids = check_ids(execute_query_array("SELECT id FROM tbl_perms WHERE owner='all'"));

        $rows1 = execute_query_array("SELECT * FROM tbl_users_apps_perms WHERE perm_id IN ($perms_ids)");
        db_query("DELETE FROM tbl_users_apps_perms WHERE perm_id IN ($perms_ids)");

        $rows2 = execute_query_array("SELECT * FROM tbl_groups_apps_perms WHERE perm_id IN ($perms_ids)");
        db_query("DELETE FROM tbl_groups_apps_perms WHERE perm_id IN ($perms_ids)");

        test_external_exec('php/perms6.php', '', '');
        test_external_exec('php/perms7.php', '', '');

        foreach ($rows1 as $row) {
            db_query(make_insert_query('tbl_users_apps_perms', $row));
        }

        foreach ($rows2 as $row) {
            db_query(make_insert_query('tbl_groups_apps_perms', $row));
        }

        $token = $json['token'];
        $row = execute_query("SELECT * FROM tbl_users_tokens WHERE token='$token'");
        set_data('server/token', $row['token']);
        set_data('server/remote_addr', $row['remote_addr']);
        set_data('server/user_agent', $row['user_agent']);

        $this->assertSame(check_app_perm_id('users', 'view', 1), true);
        $this->assertSame(__user_is_admin('users'), true);

        set_data('server/token', 'a');
        set_data('server/remote_addr', 'b');
        set_data('server/user_agent', 'c');

        $this->assertSame(__user_is_admin('users'), false);

        $this->assertSame(merge_data_actions([], ''), []);
        $this->assertSame(merge_data_actions([], [
            [
                'app' => 'configlog',
                'action' => 'create',
            ],
        ]), []);
    }
}
