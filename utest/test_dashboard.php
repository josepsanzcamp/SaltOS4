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
 * Test dashboard
 *
 * This test performs some tests to validate the correctness
 * of the dashboard functions
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
require_once 'apps/dashboard/php/dashboard.php';

/**
 * Main class of this unit test
 */
final class test_dashboard extends TestCase
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
        $this->assertArrayHasKey('token', $json);
        return $json;
    }

    #[Depends('test_authtoken')]
    #[testdox('dashboard functions')]
    /**
     * dashboard test
     *
     * This test performs some tests to validate the correctness
     * of the dashboard functions
     */
    public function test_dashboard(array $json): void
    {
        $token = $json['token'];
        $row = execute_query("SELECT * FROM tbl_users_tokens WHERE token='$token'");
        set_data('server/token', $row['token']);
        set_data('server/remote_addr', $row['remote_addr']);
        set_data('server/user_agent', $row['user_agent']);

        $items = __dashboard_helper();
        $button_ids = array_column($this->collect($items, 'button'), 'id');
        $this->assertContains('button_users', $button_ids);
        $alert_ids = array_column($this->collect($items, 'alert'), 'id');
        $this->assertNotEmpty($alert_ids);

        $menu = __navbar_helper();
        $onclicks = array_column($this->collect($menu, 'item'), 'onclick');
        $this->assertContains("saltos.window.open('app/users')", $onclicks);

        // A guest with no valid credentials only gets the public apps, if any
        set_data('server/token', 'nada');
        set_data('server/remote_addr', 'nada');
        set_data('server/user_agent', 'nada');

        $items = __dashboard_helper();
        $button_ids = array_column($this->collect($items, 'button'), 'id');
        $this->assertNotContains('button_users', $button_ids);

        $menu = __navbar_helper();
        $onclicks = array_column($this->collect($menu, 'item'), 'onclick');
        $this->assertNotContains("saltos.window.open('app/users')", $onclicks);

        // Restore the server data to avoid leaking state into other tests
        set_data('server', null);
    }

    /**
     * Collect helper
     *
     * xml2array()/set_array() store repeated elements as "name", "name#1",
     * "name#2", ... instead of a plain list, this helper flattens them back
     * into a plain list of their #attr arrays, to ease the assertions above
     */
    private function collect(array $items, string $name): array
    {
        $result = [];
        foreach ($items as $key => $val) {
            if ($key === $name || str_starts_with((string) $key, "$name#")) {
                $result[] = $val['#attr'] ?? [];
            }
        }
        return $result;
    }
}
