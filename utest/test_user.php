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
require_once 'php/lib/browser.php';

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

        $browser = get_browser_platform_device_type();
        $this->assertIsArray($browser);
        $this->assertArrayHasKey('browser', $browser);
        $this->assertArrayHasKey('platform', $browser);
        $this->assertArrayHasKey('device_type', $browser);
        $this->assertSame($browser['browser'], 'Default Browser');
        $this->assertSame($browser['platform'], 'unknown');
        $this->assertSame($browser['device_type'], 'unknown');

        $browser = get_browser_platform_device_type('nada');
        $this->assertIsArray($browser);
        $this->assertArrayHasKey('browser', $browser);
        $this->assertArrayHasKey('platform', $browser);
        $this->assertArrayHasKey('device_type', $browser);
        $this->assertSame($browser['browser'], 'Default Browser');
        $this->assertSame($browser['platform'], 'unknown');
        $this->assertSame($browser['device_type'], 'unknown');

        set_data(
            'server/user_agent',
            'Mozilla/5.0 (X11; Linux x86_64; rv:133.0) Gecko/20100101 Firefox/133.0'
        );

        $browser = get_browser_platform_device_type(get_data('server/user_agent'));
        $this->assertIsArray($browser);
        $this->assertArrayHasKey('browser', $browser);
        $this->assertArrayHasKey('platform', $browser);
        $this->assertArrayHasKey('device_type', $browser);
        $this->assertSame($browser['browser'], 'Firefox');
        $this->assertSame($browser['platform'], 'Linux');
        $this->assertSame($browser['device_type'], 'Desktop');
    }
}
