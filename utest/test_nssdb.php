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
 * Test nssdb
 *
 * This test performs some tests to validate the correctness
 * of the nssdb functions
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
require_once 'apps/certs/php/nssdb.php';
require_once 'apps/certs/php/certs.php';
require_once 'apps/common/php/actions.php';

/**
 * Main class of this unit test
 */
final class test_nssdb extends TestCase
{
    #[testdox('nssdb functions')]
    /**
     * nssdb test
     *
     * This test performs some tests to validate the correctness
     * of the nssdb functions
     */
    public function test_nssdb(): void
    {
        if (file_exists('data/files/nssdb')) {
            $files = glob('data/files/nssdb/*');
            foreach ($files as $file) {
                unlink($file);
            }
            rmdir('data/files/nssdb');
        }
        $this->assertSame(__nssdb_list(), []);

        $this->assertDirectoryDoesNotExist('data/files/nssdb');
        $this->assertSame(__nssdb_init(), []);
        $this->assertDirectoryExists('data/files/nssdb');
        $this->assertSame(__nssdb_list(), []);

        $certfile = 'data/files/certificate.p12';
        $this->assertFileDoesNotExist($certfile);
        $info = __nssdb_create(
            $certfile,
            '1234',
            '/C=ES/serialNumber=ABCDE-12341234A/O=23452345B/CN=THE SALTOS PROJECT',
            'THE SALTOS PROJECT - 34563456C',
        );
        $this->assertMatchesRegularExpression('/^[+.*]+$/', implode('', $info));
        $this->assertFileExists($certfile);

        $this->assertSame(__nssdb_add($certfile, '1234'), ['pk12util: PKCS12 IMPORT SUCCESSFUL']);
        $info = __nssdb_list();
        $this->assertIsArray($info);
        $this->assertCount(1, $info);
        $this->assertSame($info[0], 'THE SALTOS PROJECT - 34563456C');

        unlink($certfile);
        $nick = $info[0];
        $this->assertSame($nick, 'THE SALTOS PROJECT - 34563456C');

        $info = __nssdb_info($nick);
        $this->assertIsArray($info);
        $this->assertCount(2, $info);
        $this->assertArrayHasKey('subject', $info);
        $this->assertArrayHasKey('info', $info);
        $this->assertCount(4, $info['subject']);
        $this->assertSame($info['subject']['countryName'], 'ES');
        $this->assertSame($info['subject']['serialNumber'], 'ABCDE-12341234A');
        $this->assertSame($info['subject']['organizationName'], '23452345B');
        $this->assertSame($info['subject']['commonName'], 'THE SALTOS PROJECT');
        $this->assertCount(7, $info['info']);
        $this->assertArrayHasKey('serialNumber', $info['info']);
        $this->assertArrayHasKey('validFrom', $info['info']);
        $this->assertArrayHasKey('validTo', $info['info']);
        $this->assertArrayHasKey('signatureType', $info['info']);
        $this->assertArrayHasKey('md5', $info['info']);
        $this->assertArrayHasKey('sha1', $info['info']);
        $this->assertArrayHasKey('sha256', $info['info']);

        $info = __nssdb_info($nick, true);
        $this->assertIsArray($info);
        $this->assertCount(2, $info);
        $this->assertArrayHasKey('subject', $info);
        $this->assertArrayHasKey('info', $info);
        $this->assertCount(4, $info['subject']);
        $this->assertSame($info['subject']['C'], 'ES');
        $this->assertSame($info['subject']['serialNumber'], 'ABCDE-12341234A');
        $this->assertSame($info['subject']['O'], '23452345B');
        $this->assertSame($info['subject']['CN'], 'THE SALTOS PROJECT');
        $this->assertCount(7, $info['info']);
        $this->assertArrayHasKey('serialNumber', $info['info']);
        $this->assertArrayHasKey('validFrom', $info['info']);
        $this->assertArrayHasKey('validTo', $info['info']);
        $this->assertArrayHasKey('signatureType', $info['info']);
        $this->assertArrayHasKey('md5', $info['info']);
        $this->assertArrayHasKey('sha1', $info['info']);
        $this->assertArrayHasKey('sha256', $info['info']);

        $info = __nssdb_info($nick, false);
        $this->assertIsArray($info);
        $this->assertCount(2, $info);
        $this->assertArrayHasKey('subject', $info);
        $this->assertArrayHasKey('info', $info);
        $this->assertCount(4, $info['subject']);
        $this->assertSame($info['subject']['countryName'], 'ES');
        $this->assertSame($info['subject']['serialNumber'], 'ABCDE-12341234A');
        $this->assertSame($info['subject']['organizationName'], '23452345B');
        $this->assertSame($info['subject']['commonName'], 'THE SALTOS PROJECT');
        $this->assertCount(7, $info['info']);
        $this->assertArrayHasKey('serialNumber', $info['info']);
        $this->assertArrayHasKey('validFrom', $info['info']);
        $this->assertArrayHasKey('validTo', $info['info']);
        $this->assertArrayHasKey('signatureType', $info['info']);
        $this->assertArrayHasKey('md5', $info['info']);
        $this->assertArrayHasKey('sha1', $info['info']);
        $this->assertArrayHasKey('sha256', $info['info']);

        $input = '../../utest/files/input1.pdf';
        $middle = get_cache_file($input, '.pdf');
        $output = 'data/files/output1.pdf';

        if (file_exists($middle)) {
            unlink($middle);
        }
        $this->assertFileDoesNotExist($middle);
        $this->assertSame(__nssdb_update($nick, $input), $middle);
        $this->assertFileExists($middle);

        if (file_exists($output)) {
            unlink($output);
        }
        $this->assertFileDoesNotExist($output);
        $info = __nssdb_pdfsig($nick, $middle, $output);
        $this->assertFileExists($output);

        $this->assertIsArray($info);
        $this->assertSame($info[0], "Digital Signature Info of: $output");
        $this->assertSame($info[1], 'Signature #1:');
        $this->assertStringContainsString('ABCDE-12341234A', implode('', $info));
        $this->assertStringContainsString('23452345B', implode('', $info));
        $this->assertStringContainsString('THE SALTOS PROJECT', implode('', $info));

        $this->assertGreaterThan(filesize($input), filesize($middle));
        $this->assertGreaterThan(filesize($middle), filesize($output));
        unlink($middle);
        unlink($output);

        $input = '../../utest/files/input2.pdf';
        $middle = get_cache_file($input, '.pdf');
        $output = 'data/files/output2.pdf';

        if (file_exists($middle)) {
            unlink($middle);
        }
        $this->assertFileDoesNotExist($middle);
        $this->assertSame(__nssdb_update($nick, $input), $middle);
        $this->assertFileExists($middle);

        if (file_exists($output)) {
            unlink($output);
        }
        $this->assertFileDoesNotExist($output);
        $info = __nssdb_pdfsig($nick, $middle, $output);
        $this->assertFileExists($output);

        $this->assertIsArray($info);
        $this->assertSame($info[0], "Digital Signature Info of: $output");
        $this->assertSame($info[1], 'Signature #1:');
        $this->assertStringContainsString('ABCDE-12341234A', implode('', $info));
        $this->assertStringContainsString('23452345B', implode('', $info));
        $this->assertStringContainsString('THE SALTOS PROJECT', implode('', $info));

        $this->assertGreaterThan(filesize($input), filesize($middle));
        $this->assertGreaterThan(filesize($middle), filesize($output));
        unlink($middle);
        unlink($output);

        // create a default subject and name certificate
        $certfile = 'data/files/certificate.p12';
        $this->assertFileDoesNotExist($certfile);
        $info = __nssdb_create($certfile, '1234');
        $this->assertMatchesRegularExpression('/^[+.*]+$/', implode('', $info));
        $this->assertFileExists($certfile);

        $this->assertSame(__nssdb_add($certfile, '1234'), ['pk12util: PKCS12 IMPORT SUCCESSFUL']);
        $info = __nssdb_list();
        $this->assertIsArray($info);
        $this->assertCount(2, $info);
        $this->assertSame($info[0], 'THE SALTOS PROJECT - 34563456C');
        $this->assertSame($info[1], 'THE SALTOS PROJECT - 12345678X');

        unlink($certfile);
        $nick2 = $info[1];
        $this->assertSame($nick2, 'THE SALTOS PROJECT - 12345678X');

        $info = __nssdb_info($nick2);
        $this->assertIsArray($info);
        $this->assertCount(2, $info);
        $this->assertArrayHasKey('subject', $info);
        $this->assertArrayHasKey('info', $info);
        $this->assertCount(4, $info['subject']);
        $this->assertSame($info['subject']['countryName'], 'ES');
        $this->assertSame($info['subject']['serialNumber'], 'ABCDE-12345678X');
        $this->assertSame($info['subject']['organizationName'], '12345678X');
        $this->assertSame($info['subject']['commonName'], 'THE SALTOS PROJECT');
        $this->assertCount(7, $info['info']);
        $this->assertArrayHasKey('serialNumber', $info['info']);
        $this->assertArrayHasKey('validFrom', $info['info']);
        $this->assertArrayHasKey('validTo', $info['info']);
        $this->assertArrayHasKey('signatureType', $info['info']);
        $this->assertArrayHasKey('md5', $info['info']);
        $this->assertArrayHasKey('sha1', $info['info']);
        $this->assertArrayHasKey('sha256', $info['info']);

        $this->assertSame(__nssdb_remove($nick), []);
        $info = __nssdb_list();
        $this->assertIsArray($info);
        $this->assertCount(1, $info);
        $this->assertSame($info[0], 'THE SALTOS PROJECT - 12345678X');

        $this->assertSame(__nssdb_remove($nick2), []);
        $this->assertSame(__nssdb_list(), []);

        $this->assertSame(__nssdb_reset(), []);
        $this->assertSame(__nssdb_list(), []);
    }

    #[testdox('certs functions')]
    /**
     * certs test
     *
     * This test performs some tests to validate the correctness
     * of the certs functions
     */
    public function test_certs(): void
    {
        $json = test_cli_helper('app/certs/list/table', '', '', '', 'admin');
        $this->assertArrayHasKey('data', $json);
        $this->assertCount(0, $json['data']);

        $files = glob('apps/certs/sample/certs/*.p12');
        $files = array_slice($files, 0, 3);
        foreach ($files as $index => $file) {
            $id = get_unique_id_md5();
            $app = 'app/certs/create';
            $name = basename($file);
            $size = filesize($file);
            $type = saltos_content_type($file);
            $data = mime_inline($type, file_get_contents($file));
            $file2 = [
                'id' => $id,
                'app' => $app,
                'name' => $name,
                'size' => $size,
                'type' => $type,
                'data' => $data,
                'error' => '',
                'file' => '',
                'hash' => '',
            ];
            $json = test_cli_helper('upload/addfile', $file2, '', '', 'admin');
            $file2['data'] = '';
            $file2['file'] = execute_query("SELECT file FROM tbl_uploads WHERE uniqid='$id'");
            $file2['hash'] = md5_file($file);
            $this->assertSame($json, $file2);
            $files[$index] = $json;
        }

        $json = test_cli_helper('app/certs/insert', [
            'passfile' => '1234',
            'certfile' => $files,
        ], '', '', 'admin');
        $this->assertCount(1, $json);
        $this->assertArrayHasKey('status', $json);
        $this->assertSame($json['status'], 'ok');

        $json = test_cli_helper('app/certs/insert', [
            'passfile' => '1234',
            'certfile' => $files,
        ], '', '', 'admin');
        $this->assertCount(3, $json);
        $this->assertArrayHasKey('status', $json);
        $this->assertSame($json['status'], 'ko');
        $this->assertArrayHasKey('text', $json);
        $this->assertSame($json['text'], 'Error importing certificates');
        $this->assertArrayHasKey('code', $json);

        $json = test_cli_helper('app/certs/list/table', [], '', '', 'admin');
        $this->assertArrayHasKey('data', $json);
        $this->assertCount(3, $json['data']);
        $this->assertArrayHasKey('footer', $json);
        $this->assertSame($json['footer'], 'Total: 3');
        $ids = array_column($json['data'], 'id');

        $json = test_cli_helper('app/certs/list/table', [
            'page' => 1,
        ], '', '', 'admin');
        $this->assertArrayHasKey('data', $json);
        $this->assertCount(0, $json['data']);
        $this->assertArrayNotHasKey('footer', $json);

        $json = test_cli_helper('app/certs/list/table', [
            'search' => '+ nada',
        ], '', '', 'admin');
        $this->assertArrayHasKey('data', $json);
        $this->assertCount(0, $json['data']);

        $json = test_cli_helper("app/certs/view/{$ids[0]}", [], '', '', 'admin');
        $this->assertArrayHasKey('data', $json);
        $this->assertCount(2, $json['data']);
        $this->assertArrayHasKey('name', $json['data']);
        $this->assertArrayHasKey('info', $json['data']);

        $json = test_cli_helper('app/certs/delete/nada', [], '', '', 'admin');
        $this->assertCount(1, $json);
        $this->assertArrayHasKey('error', $json);
        $this->assertCount(2, $json['error']);
        $this->assertArrayHasKey('text', $json['error']);
        $this->assertSame($json['error']['text'], 'Permission denied');
        $this->assertArrayHasKey('code', $json['error']);

        foreach ($ids as $id) {
            $json = test_cli_helper("app/certs/delete/$id", [], '', '', 'admin');
            $this->assertCount(1, $json);
            $this->assertArrayHasKey('status', $json);
            $this->assertSame($json['status'], 'ok');
        }

        $json = test_cli_helper('app/certs/list/table', '', '', '', 'admin');
        $this->assertArrayHasKey('data', $json);
        $this->assertCount(0, $json['data']);

        $json = __certs_view('nada');
        $this->assertCount(3, $json);
        $this->assertArrayHasKey('status', $json);
        $this->assertSame($json['status'], 'ko');
        $this->assertArrayHasKey('text', $json);
        $this->assertSame($json['text'], 'Nick not found');
        $this->assertArrayHasKey('code', $json);

        $json = __certs_delete('nada');
        $this->assertCount(3, $json);
        $this->assertArrayHasKey('status', $json);
        $this->assertSame($json['status'], 'ko');
        $this->assertArrayHasKey('text', $json);
        $this->assertSame($json['text'], 'Nick not found');
        $this->assertArrayHasKey('code', $json);
    }

    #[testdox('actions functions')]
    /**
     * actions test
     *
     * This test performs some tests to validate the correctness
     * of the certs functions
     */
    public function test_actions(): void
    {
        $data = __merge_data_actions([
            ['id' => '1', 'name' => 'name 1'],
        ], [
            ['app' => 'certs', 'action' => 'view'],
        ]);
        $this->assertNotSame($data, []);

        test_external_exec('php/nssdb1.php', 'phperror.log', 'actions must be an array');

        $data = __merge_data_actions([
            ['id' => '1', 'name' => 'name 1'],
        ], []);
        $this->assertSame($data, [
            ['id' => '1', 'name' => 'name 1'],
        ]);

        $data = __merge_data_actions([
            ['id' => '1', 'name' => 'name 1'],
        ], [
            ['app' => 'certs', 'action' => 'edit'],
        ]);
        $this->assertSame($data, [
            ['id' => '1', 'name' => 'name 1'],
        ]);
    }
}
