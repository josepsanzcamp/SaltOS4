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
 * Test system
 *
 * This test performs some tests to validate the correctness
 * of the system functions
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
require_once 'php/lib/apache.php';

/**
 * Main class of this unit test
 */
final class test_system extends TestCase
{
    #[testdox('system functions')]
    /**
     * system test
     *
     * This test performs some tests to validate the correctness
     * of the system functions
     */
    public function test_system(): void
    {
        $array = check_system();
        $this->assertCount(0, $array);

        if (file_exists('data/nada')) {
            rmdir('data/nada');
        }
        $this->assertDirectoryDoesNotExist('data/nada');
        mkdir('data/nada', 0444);
        $this->assertDirectoryExists('data/nada');

        $array = check_directories();
        $this->assertCount(1, $array);
        $this->assertSame($array[0]['error'], 'data/nada not writable');
        $this->assertDirectoryExists('data/nada');

        $json = test_cli_helper('setup', [], '', '', '');
        $this->assertCount(3, $json);
        $this->assertArrayHasKey('system', $json);
        $this->assertCount(0, $json['system']['output']);
        $this->assertArrayHasKey('directories', $json);
        $this->assertArrayHasKey('error', $json['directories']['output']['0']);
        $this->assertCount(2, $json['directories']['output']['0']);
        $this->assertArrayHasKey('error', $json['directories']['output']['0']);
        $this->assertArrayHasKey('details', $json['directories']['output']['0']);
        $this->assertSame($json['directories']['output']['0']['error'], 'data/nada not writable');
        $this->assertArrayHasKey('composer', $json);
        $this->assertCount(0, $json['composer']['output']);

        $this->assertDirectoryExists('data/nada');
        rmdir('data/nada');
        $this->assertDirectoryDoesNotExist('data/nada');

        $json = test_cli_helper('setup/apache http://127.0.0.1:8080/api', [], '', '', '');
        $this->assertCount(1, $json);
        $this->assertArrayHasKey('apache', $json);
        $this->assertCount(3, $json['apache']);
        $this->assertArrayHasKey('time', $json['apache']);
        $this->assertArrayHasKey('output', $json['apache']);
        $this->assertArrayHasKey('count', $json['apache']);
        //~ $this->assertSame($json['apache']['output'], []);
        //~ $this->assertSame($json['apache']['count'], 0);

        // Cover the "url not reachable" error branch of check_apache()
        $result = check_apache('http://127.0.0.1:1');
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('error', $result[0]);

        // Cover the "X-About header not found" error branch of
        // check_apache(), by pointing it at the static web root
        // instead of the API endpoint
        $result = check_apache('http://127.0.0.1:8080');
        $this->assertCount(1, $result);
        $this->assertSame('X-About header not found', $result[0]['error']);

        // Cover the "non 200 OK" error branch of check_apache(), by
        // pointing it at a real directory with no index file, which
        // genuinely 404s under the built-in PHP web server too
        $result = check_apache('http://127.0.0.1:8080/img');
        $this->assertCount(1, $result);
        $this->assertStringContainsString('404', $result[0]['error']);

        // Cover the "Authorization Bearer header not found" error
        // branch of check_apache(): a url that already carries a
        // query string breaks the "$url/?/auth/test" concatenation,
        // so the auth/test route is never actually reached
        $result = check_apache('http://127.0.0.1:8080/api?dummy=1');
        $this->assertCount(2, $result);
        $this->assertSame('Authorization Bearer header not found', $result[1]['error']);

        // Cover the "Server header found" warning branch of
        // check_apache(): php -S itself never sends a Server header
        // (verified even for a plain static file), but a script is
        // free to set one explicitly, so drop a throwaway endpoint
        // into the already-running httpstart docroot that does just
        // that and mimics enough of the real API to pass the earlier
        // checks (X-About header, echoing back the Bearer token)
        file_put_contents('../web/zzz_apache_test.php', <<<'PHP'
            <?php
            header('X-About: SaltOS test');
            if (str_contains($_SERVER['QUERY_STRING'] ?? '', 'auth/test')) {
                header('Content-Type: application/json');
                $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
                echo json_encode(['token' => trim(str_ireplace('Bearer', '', $auth))]);
            } else {
                header('Server: TestServer');
            }
            PHP);
        $result = check_apache('http://127.0.0.1:8080/zzz_apache_test.php');
        unlink('../web/zzz_apache_test.php');
        $serverWarnings = array_values(array_filter(
            $result,
            fn($r) => isset($r['warning']) && str_starts_with($r['warning'], 'Server ')
        ));
        $this->assertCount(1, $serverWarnings);
        $this->assertSame('Server TestServer header found', $serverWarnings[0]['warning']);

        // Cover the tc-lib-pdf "example" directories loop of
        // check_apache(): no local vendor copy currently ships any,
        // so fake one to exercise the glob() match and its two urls
        mkdir('lib/tc-lib-pdf/vendor/tecnickcom/zzztest/example', 0777, true);
        file_put_contents('lib/tc-lib-pdf/vendor/tecnickcom/zzztest/example/index.php', '<?php');
        $result = check_apache('http://127.0.0.1:8080/api');
        $this->assertIsArray($result);
        unlink('lib/tc-lib-pdf/vendor/tecnickcom/zzztest/example/index.php');
        rmdir('lib/tc-lib-pdf/vendor/tecnickcom/zzztest/example');
        rmdir('lib/tc-lib-pdf/vendor/tecnickcom/zzztest');

        // Cover the three "skip" branches of check_composer(): a stale
        // *.old package directory, a composer.lock without a "packages"
        // key, and a package without a "require" key
        mkdir('lib/zzztest.old');
        file_put_contents('lib/zzztest.old/composer.lock', json_encode(['packages' => []]));
        mkdir('lib/zzztest_nopkgs');
        file_put_contents('lib/zzztest_nopkgs/composer.lock', json_encode(['foo' => 'bar']));
        mkdir('lib/zzztest_norequire');
        file_put_contents('lib/zzztest_norequire/composer.lock', json_encode([
            'packages' => [['name' => 'zzztest/norequire']],
        ]));
        $array = check_composer();
        $this->assertCount(0, $array);
        unlink('lib/zzztest.old/composer.lock');
        rmdir('lib/zzztest.old');
        unlink('lib/zzztest_nopkgs/composer.lock');
        rmdir('lib/zzztest_nopkgs');
        unlink('lib/zzztest_norequire/composer.lock');
        rmdir('lib/zzztest_norequire');

        $json = test_cli_helper('setup/crm', [], '', '', '');
        $this->assertCount(1, $json);
        $this->assertArrayHasKey('setup', $json);
        $this->assertCount(2, $json['setup']);
        $this->assertArrayHasKey('time', $json['setup']);
        $this->assertArrayHasKey('total', $json['setup']);

        $json = test_cli_helper('setup/certs', [], '', '', '');
        $this->assertCount(1, $json);
        $this->assertArrayHasKey('setup', $json);
        $this->assertCount(2, $json['setup']);
        $this->assertArrayHasKey('time', $json['setup']);
        $this->assertArrayHasKey('total', $json['setup']);

        exec_check_system();
    }
}
