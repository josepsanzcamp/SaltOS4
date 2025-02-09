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
require_once 'apps/certs/php/nssdb.php';

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
}
