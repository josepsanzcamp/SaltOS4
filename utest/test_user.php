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
require_once 'php/lib/geoip.php';
require_once 'php/lib/security.php';

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

    #[testdox('browser functions')]
    /**
     * browser test
     *
     * This test performs some tests to validate the correctness
     * of the browser functions
     */
    public function test_browser(): void
    {
        $browser = get_browser_array();
        $this->assertIsArray($browser);
        $this->assertArrayHasKey('browser', $browser);
        $this->assertArrayHasKey('platform', $browser);
        $this->assertArrayHasKey('device_type', $browser);
        $this->assertSame($browser['browser'], 'Default Browser');
        $this->assertSame($browser['platform'], 'unknown');
        $this->assertSame($browser['device_type'], 'unknown');

        $browser = get_browser_array('nada');
        $this->assertIsArray($browser);
        $this->assertArrayHasKey('browser', $browser);
        $this->assertArrayHasKey('platform', $browser);
        $this->assertArrayHasKey('device_type', $browser);
        $this->assertSame($browser['browser'], 'Default Browser');
        $this->assertSame($browser['platform'], 'unknown');
        $this->assertSame($browser['device_type'], 'unknown');

        $user_agent = 'Mozilla/5.0 (X11; Linux x86_64; rv:133.0) Gecko/20100101 Firefox/133.0';

        $browser = get_browser_array($user_agent);
        $this->assertIsArray($browser);
        $this->assertArrayHasKey('browser', $browser);
        $this->assertArrayHasKey('platform', $browser);
        $this->assertArrayHasKey('device_type', $browser);
        $this->assertSame($browser['browser'], 'Firefox');
        $this->assertSame($browser['platform'], 'Linux');
        $this->assertSame($browser['device_type'], 'Desktop');

        set_data('server/lang', 'en_US');
        $browser = get_browser_string($user_agent);
        $this->assertSame($browser, 'Firefox browser, Linux platform, and a Desktop device type');

        set_data('server/lang', 'es_ES');
        $browser = get_browser_string($user_agent);
        $this->assertSame($browser, 'navegador Firefox, plataforma Linux y dispositivo tipo Desktop');

        set_data('server/lang', 'ca_ES');
        $browser = get_browser_string($user_agent);
        $this->assertSame($browser, 'navegador Firefox, plataforma Linux i dispositiu tipus Desktop');

        set_data('server/lang', null);
    }

    #[testdox('geoip functions')]
    /**
     * geoip test
     *
     * This test performs some tests to validate the correctness
     * of the geoip functions
     */
    public function test_geoip(): void
    {
        $files = glob('data/cache/*.geoip');
        foreach ($files as $file) {
            unlink($file);
        }

        $geoip = get_geoip_array('1.1.1.1');
        $this->assertIsArray($geoip);
        $this->assertArrayHasKey('city', $geoip);
        $this->assertArrayHasKey('region', $geoip);
        $this->assertArrayHasKey('country', $geoip);
        $this->assertArrayHasKey('ip', $geoip);
        $this->assertArrayHasKey('isp', $geoip);
        $this->assertContains($geoip['city'], ['', '']);
        $this->assertContains($geoip['region'], ['', '']);
        $this->assertContains($geoip['country'], ['', '']);
        $this->assertContains($geoip['ip'], ['', '1.1.1.1']);
        $this->assertContains($geoip['isp'], ['', 'Cloudflare, Inc.']);

        $geoip = get_geoip_array('84.88.65.140');
        $this->assertIsArray($geoip);
        $this->assertArrayHasKey('city', $geoip);
        $this->assertArrayHasKey('region', $geoip);
        $this->assertArrayHasKey('country', $geoip);
        $this->assertArrayHasKey('ip', $geoip);
        $this->assertArrayHasKey('isp', $geoip);
        $this->assertContains($geoip['city'], ['', '']);
        $this->assertContains($geoip['region'], ['', '']);
        $this->assertContains($geoip['country'], ['', 'Spain']);
        $this->assertContains($geoip['ip'], ['', '84.88.65.140']);
        $this->assertContains($geoip['isp'], ['', 'Consorci de Serveis Universitaris de Catalunya']);

        $geoip = get_geoip_array('147.83.2.135');
        $this->assertIsArray($geoip);
        $this->assertArrayHasKey('city', $geoip);
        $this->assertArrayHasKey('region', $geoip);
        $this->assertArrayHasKey('country', $geoip);
        $this->assertArrayHasKey('ip', $geoip);
        $this->assertArrayHasKey('isp', $geoip);
        $this->assertContains($geoip['city'], ['', 'Banyoles']);
        $this->assertContains($geoip['region'], ['', 'Catalonia']);
        $this->assertContains($geoip['country'], ['', 'Spain']);
        $this->assertContains($geoip['ip'], ['', '147.83.2.135']);
        $this->assertContains($geoip['isp'], ['', 'Consorci de Serveis Universitaris de Catalunya']);

        $geoip = get_geoip_array('79.116.196.1');
        $this->assertIsArray($geoip);
        $this->assertArrayHasKey('city', $geoip);
        $this->assertArrayHasKey('region', $geoip);
        $this->assertArrayHasKey('country', $geoip);
        $this->assertArrayHasKey('ip', $geoip);
        $this->assertArrayHasKey('isp', $geoip);
        $this->assertContains($geoip['city'], ['', 'Madrid']);
        $this->assertContains($geoip['region'], ['', 'Madrid']);
        $this->assertContains($geoip['country'], ['', 'Spain']);
        $this->assertContains($geoip['ip'], ['', '79.116.196.1']);
        $this->assertContains($geoip['isp'], ['', 'Digi Spain Telecom S.A']);

        $remote_addr = '79.116.196.1';

        set_data('server/lang', 'en_US');
        $geoip = get_geoip_string($remote_addr);
        $this->assertContains($geoip, [
            ', ,  using IP  and network of ',
            'Madrid, Madrid, Spain using IP 79.116.196.1 and network of Digi Spain Telecom S.A',
        ]);

        set_data('server/lang', 'es_ES');
        $geoip = get_geoip_string($remote_addr);
        $this->assertContains($geoip, [
            ', ,  usando la IP  y la red de ',
            'Madrid, Madrid, Spain usando la IP 79.116.196.1 y la red de Digi Spain Telecom S.A',
        ]);

        set_data('server/lang', 'ca_ES');
        $geoip = get_geoip_string($remote_addr);
        $this->assertContains($geoip, [
            ', ,  fent servir la IP  i la xarxa de ',
            'Madrid, Madrid, Spain fent servir la IP 79.116.196.1 i la xarxa de Digi Spain Telecom S.A',
        ]);

        set_data('server/lang', null);
    }

    #[testdox('security functions')]
    /**
     * security test
     *
     * This test performs some tests to validate the correctness
     * of the security functions
     */
    public function test_security(): void
    {
        $remote_addr = '79.116.196.1';
        $user_agent = 'Mozilla/5.0 (X11; Linux x86_64; rv:133.0) Gecko/20100101 Firefox/133.0';

        set_data('server/lang', 'en_US');
        $security = get_connection_detected($remote_addr, $user_agent);
        $this->assertSame($security, 'A connection has been detected from ' .
            get_geoip_string($remote_addr) . ' on a ' . get_browser_string($user_agent) . '.');

        set_data('server/lang', 'es_ES');
        $security = get_connection_detected($remote_addr, $user_agent);
        $this->assertSame($security, 'Se ha detectado una conexión desde ' .
            get_geoip_string($remote_addr) . ' con ' . get_browser_string($user_agent) . '.');

        set_data('server/lang', 'ca_ES');
        $security = get_connection_detected($remote_addr, $user_agent);
        $this->assertSame($security, 'S\'ha detectat una connexió des de ' .
            get_geoip_string($remote_addr) . ' amb ' . get_browser_string($user_agent) . '.');

        set_data('server/lang', null);
    }
}
