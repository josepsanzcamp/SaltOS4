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
require_once 'apps/users/php/days.php';
require_once 'apps/users/php/matrix.php';
require_once 'apps/users/php/users.php';
require_once 'apps/users/php/groups.php';

/**
 * Main class of this unit test
 */
final class test_users extends TestCase
{
    #[testdox('days functions')]
    /**
     * days test
     *
     * This test performs some tests to validate the correctness
     * of the days functions
     */
    public function test_days(): void
    {
        $this->assertSame(days2bin(null), null);
        $this->assertSame(days2bin(''), '0000000');
        $this->assertSame(days2bin('0'), '0000000');
        $this->assertSame(days2bin('1'), '0000001');
        $this->assertSame(days2bin('2'), '0000010');
        $this->assertSame(days2bin('4'), '0000100');
        $this->assertSame(days2bin('8'), '0001000');
        $this->assertSame(days2bin('16'), '0010000');
        $this->assertSame(days2bin('32'), '0100000');
        $this->assertSame(days2bin('64'), '1000000');
        $this->assertSame(days2bin('127'), '1111111');

        $this->assertSame(bin2days(''), '');
        $this->assertSame(bin2days('1'), '1');
        $this->assertSame(bin2days('10'), '2');
        $this->assertSame(bin2days('100'), '4');
        $this->assertSame(bin2days('1000'), '8');
        $this->assertSame(bin2days('10000'), '16');
        $this->assertSame(bin2days('100000'), '32');
        $this->assertSame(bin2days('1000000'), '64');
        $this->assertSame(bin2days('1111111'), '1,2,4,8,16,32,64');

        $this->assertSame(fix4days(['days' => '0000001']), ['days' => '1']);
        $this->assertSame(fix4days(['days' => '0000010']), ['days' => '2']);
        $this->assertSame(fix4days(['days' => '0000100']), ['days' => '4']);
        $this->assertSame(fix4days(['days' => '0001000']), ['days' => '8']);
        $this->assertSame(fix4days(['days' => '0010000']), ['days' => '16']);
        $this->assertSame(fix4days(['days' => '0100000']), ['days' => '32']);
        $this->assertSame(fix4days(['days' => '1000000']), ['days' => '64']);
        $this->assertSame(fix4days(['days' => '1111111']), ['days' => '1,2,4,8,16,32,64']);
    }

    #[testdox('users functions')]
    /**
     * users test
     *
     * This test performs some tests to validate the correctness
     * of the users functions
     */
    public function test_users(): void
    {
        $query = 'UPDATE tbl_apps_perms SET deny = 1 WHERE id = 1';
        db_query($query);
        $query = 'UPDATE tbl_users_apps_perms SET deny = 1 WHERE id = 1';
        db_query($query);
        $query = 'UPDATE tbl_users_apps_perms SET allow = 0 WHERE id = 2';
        db_query($query);

        $result1 = make_matrix_perms('tbl_users_apps_perms', 'user_id', 1);

        // @phpstan-ignore arguments.count
        $this->assertSame(unmake_matrix_data([], [], [], null), null);

        // @phpstan-ignore arguments.count
        $result2 = unmake_matrix_data(
            execute_query_array('SELECT id FROM tbl_perms WHERE active = 1 ORDER BY id ASC'),
            execute_query_array('SELECT id FROM tbl_apps WHERE active = 1 ORDER BY id ASC'),
            execute_query_array('SELECT * FROM tbl_apps_perms'),
            $result1['data']
        );

        $query = 'UPDATE tbl_apps_perms SET deny = 0 WHERE id = 1';
        db_query($query);
        $query = 'UPDATE tbl_users_apps_perms SET deny = 0 WHERE id = 1';
        db_query($query);
        $query = 'UPDATE tbl_users_apps_perms SET allow = 1 WHERE id = 2';
        db_query($query);

        $result = insert_user([]);
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ko');

        $result = insert_user([
            'newpass' => '',
            'renewpass' => '',
        ]);
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ko');

        $result = insert_user([
            'newpass' => 'a',
            'renewpass' => 'b',
        ]);
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ko');

        $result = insert_user([
            'newpass' => 'a',
            'renewpass' => 'a',
        ]);
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ko');

        $result = insert_user([
            'nada' => 'nada',
            'newpass' => 'asd123ASD',
            'renewpass' => 'asd123ASD',
        ]);
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ko');

        $result = insert_user([
            'active' => 1,
            'group_id' => 1,
            'login' => 'test',
            'name' => 'test user',
            'description' => 'test user',
            'start' => '00:00:00',
            'end' => '23:59:59',
            'days' => '1111111',
            'newpass' => 'asd123ASD',
            'renewpass' => 'asd123ASD',
            'perms' => $result2,
        ]);
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ok');
        $user_id = $result['created_id'];

        $result = update_user($user_id, []);
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ko');

        $result = update_user($user_id, [
            'newpass' => 'a',
            'renewpass' => 'b',
        ]);
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ko');

        $result = update_user($user_id, [
            'newpass' => 'a',
            'renewpass' => 'a',
        ]);
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ko');

        $result = update_user($user_id, [
            'newpass' => 'asd123ASD',
            'renewpass' => 'asd123ASD',
        ]);
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ko');

        $result = update_user($user_id, [
            'nada' => 'nada',
            'newpass' => 'asd123ASD2',
            'renewpass' => 'asd123ASD2',
        ]);
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ko');

        $result2[0]['allow'] = 0;
        $result2[0]['deny'] = 1;

        $result = update_user($user_id, [
            'active' => 1,
            'group_id' => 1,
            'login' => 'test',
            'name' => 'test user',
            'description' => 'test user',
            'start' => '00:00:00',
            'end' => '23:59:59',
            'days' => '1111111',
            'newpass' => 'asd123ASD2',
            'renewpass' => 'asd123ASD2',
            'perms' => $result2,
        ]);
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ok');

        $result = delete_user($user_id);
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ok');
    }

    #[testdox('groups functions')]
    /**
     * groups test
     *
     * This test performs some tests to validate the correctness
     * of the groups functions
     */
    public function test_groups(): void
    {
        $result1 = make_matrix_perms('tbl_groups_apps_perms', 'group_id', 1);

        // @phpstan-ignore arguments.count
        $result2 = unmake_matrix_data(
            execute_query_array('SELECT id FROM tbl_perms WHERE active = 1 ORDER BY id ASC'),
            execute_query_array('SELECT id FROM tbl_apps WHERE active = 1 ORDER BY id ASC'),
            execute_query_array('SELECT * FROM tbl_apps_perms'),
            $result1['data']
        );

        $result = insert_group([]);
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ko');

        $result = insert_group([
            'nada' => 'nada',
        ]);
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ko');

        $result = insert_group([
            'active' => 1,
            'code' => 'test',
            'name' => 'test group',
            'description' => 'test group',
            'perms' => $result2,
        ]);
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ok');
        $group_id = $result['created_id'];

        $result = update_group($group_id, []);
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ko');

        $result = update_group($group_id, [
            'nada' => 'nada',
        ]);
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ko');

        $result2[0]['allow'] = 0;
        $result2[0]['deny'] = 1;

        $result = update_group($group_id, [
            'active' => 1,
            'code' => 'test',
            'name' => 'test group',
            'description' => 'test group',
            'perms' => $result2,
        ]);
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ok');

        $result = delete_group($group_id);
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ok');
    }
}
