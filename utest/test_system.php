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
require_once 'php/lib/server.php';

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
        $this->assertCount(0, array_filter($array, fn($x) => !isset($x['warning'])));

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
        $this->assertCount(0, array_filter($json['system']['output'], fn($x) => !isset($x['warning'])));
        $this->assertArrayHasKey('directories', $json);
        $this->assertArrayHasKey('error', $json['directories']['output']['0']);
        $this->assertCount(2, $json['directories']['output']['0']);
        $this->assertArrayHasKey('error', $json['directories']['output']['0']);
        $this->assertArrayHasKey('details', $json['directories']['output']['0']);
        $this->assertSame($json['directories']['output']['0']['error'], 'data/nada not writable');
        $this->assertArrayHasKey('composer', $json);
        $this->assertCount(0, array_filter($json['composer']['output'], fn($x) => !isset($x['warning'])));

        $this->assertDirectoryExists('data/nada');
        rmdir('data/nada');
        $this->assertDirectoryDoesNotExist('data/nada');

        $json = test_cli_helper('setup/server http://127.0.0.1:8080/api', [], '', '', '');
        $this->assertCount(1, $json);
        $this->assertArrayHasKey('server', $json);
        $this->assertCount(3, $json['server']);
        $this->assertArrayHasKey('time', $json['server']);
        $this->assertArrayHasKey('output', $json['server']);
        $this->assertArrayHasKey('count', $json['server']);
        $this->assertSame($json['server']['output'], []);
        $this->assertSame($json['server']['count'], 0);

        // Cover the "url not reachable" error branch of check_server()
        $result = check_server('http://127.0.0.1:1');
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('error', $result[0]);

        // Cover the "X-About header not found" error branch of
        // check_server(), by pointing it at the static web root
        // instead of the API endpoint
        $result = check_server('http://127.0.0.1:8080');
        $this->assertCount(1, $result);
        $this->assertSame('X-About header not found', $result[0]['error']);

        // Cover the "non 200 OK" error branch of check_server(), by
        // pointing it at a directory that does not exist, which 404s
        // under the built-in PHP web server too thanks to scripts/router.php
        $result = check_server('http://127.0.0.1:8080/zzz_not_found');
        $this->assertCount(1, $result);
        $this->assertStringContainsString('404', $result[0]['error']);

        // Cover the "Authorization Bearer header not found" error
        // branch of check_server(): a url that already carries a
        // query string breaks the "$url/?/auth/test" concatenation,
        // so the auth/test route is never actually reached
        $result = check_server('http://127.0.0.1:8080/api/?/dummy/1');
        $this->assertCount(1, $result);
        $this->assertSame('Authorization Bearer header not found', $result[0]['error']);

        // Cover the "X-Powered-By" and "Server header found" warning
        // branches of check_server(): php -S itself never sends a Server
        // header (verified even for a plain static file) and php.ini
        // sets expose_php = Off, but a script is free to set both
        // explicitly, so drop a throwaway directory into the
        // already-running docroot that acts as an unprotected
        // api directory: its index.php sets both headers and mimics
        // enough of the real API to pass the earlier checks (X-About
        // header, echoing back the Bearer token). Apache and nginx
        // replace the Server header of the script by their own, so the
        // expected Server warning depends on the header really received.
        // The directory too publishes an xml directory with a config.xml
        // file, two of the urls that check_server() expects as not found
        mkdir('../web/zzz_server_test/xml', 0777, true);
        file_put_contents('../web/zzz_server_test/xml/index.html', '<html></html>');
        file_put_contents('../web/zzz_server_test/xml/config.xml', '<root></root>');
        file_put_contents('../web/zzz_server_test/index.php', <<<'PHP'
            <?php
            header('X-About: SaltOS test');
            if (str_contains($_SERVER['QUERY_STRING'] ?? '', 'auth/test')) {
                header('Content-Type: application/json');
                $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? getallheaders()['Authorization'] ?? '';
                echo json_encode(['token' => trim(str_ireplace('Bearer', '', $auth))]);
            } else {
                header('X-Powered-By: PHP/test');
                header('Server: Apache/2.4.66 (Debian)');
            }
            PHP);
        $result = check_server('http://127.0.0.1:8080/zzz_server_test');
        $response = __url_get_contents('http://127.0.0.1:8080/zzz_server_test/');
        $server = $response['headers']['Server'] ?? '';
        unlink('../web/zzz_server_test/index.php');
        unlink('../web/zzz_server_test/xml/index.html');
        unlink('../web/zzz_server_test/xml/config.xml');
        rmdir('../web/zzz_server_test/xml');
        rmdir('../web/zzz_server_test');
        $serverWarnings = array_values(array_filter(
            $result,
            fn($r) => isset($r['warning']) && str_starts_with($r['warning'], 'Server ')
        ));
        if (str_starts_with($server, 'Apache/')) {
            $this->assertCount(1, $serverWarnings);
            $this->assertSame("Server $server header found", $serverWarnings[0]['warning']);
        } else {
            $this->assertCount(0, $serverWarnings);
        }
        $poweredWarnings = array_values(array_filter(
            $result,
            fn($r) => isset($r['warning']) && str_starts_with($r['warning'], 'X-Powered-By ')
        ));
        $this->assertCount(1, $poweredWarnings);
        $this->assertSame('X-Powered-By PHP/test header found', $poweredWarnings[0]['warning']);

        // The throwaway directory publishes two of the forbidden urls
        // (they return 200 instead of 404), so check_server() must
        // report only these two urls
        $codeWarnings = array_values(array_filter(
            $result,
            fn($r) => isset($r['warning']) && str_starts_with($r['warning'], 'Code ')
        ));
        $this->assertCount(2, $codeWarnings);
        $this->assertSame(
            'Code 200 found in http://127.0.0.1:8080/zzz_server_test/xml/, expected 404',
            $codeWarnings[0]['warning']
        );
        $this->assertSame(
            'Code 200 found in http://127.0.0.1:8080/zzz_server_test/xml/config.xml, expected 404',
            $codeWarnings[1]['warning']
        );
        $this->assertCount(3 + count($serverWarnings), $result);

        // The real API does not publish any of the forbidden urls, so
        // check_server() must not report anything
        $result = check_server('http://127.0.0.1:8080/api');
        $this->assertSame([], $result);

        $array = check_composer();
        $this->assertCount(0, array_filter($array, fn($x) => !isset($x['warning'])));

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
