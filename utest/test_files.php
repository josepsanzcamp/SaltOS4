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
 * Test files
 *
 * This test performs some tests to validate the correctness
 * of the files feature
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
require_once 'php/lib/files.php';
require_once 'php/lib/notes.php';
require_once 'apps/common/php/files.php';

/**
 * Main class of this unit test
 */
final class test_files extends TestCase
{
    #[testdox('files functions')]
    /**
     * files test
     *
     * This test performs some tests to validate the correctness
     * of the files feature
     */
    public function test_files(): void
    {
        $this->assertFalse(check_files_old('dashboard', 'main'));
        $this->assertFalse(check_files_old('dashboard', 'view'));
        $this->assertFalse(check_files_old('configlog', 'view'));
        $this->assertTrue(check_files_old('customers', 'view'));
        $this->assertFalse(check_files_old('customers', 'view', 0));

        $this->assertFalse(check_files_new('dashboard', 'main'));
        $this->assertFalse(check_files_new('dashboard', 'edit'));
        $this->assertFalse(check_files_new('configlog', 'edit'));
        $this->assertTrue(check_files_new('customers', 'edit'));

        // Add a file to the tbl_uploads
        $id = get_unique_id_md5();
        $file = '../../utest/files/lorem.html';
        $app = 'app/test/file';
        $name = basename($file);
        $size = filesize($file);
        $type = saltos_content_type($file);
        $data = mime_inline($type, file_get_contents($file));
        $file1 = [
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
        $json = test_cli_helper('upload/addfile', $file1, '', '', 'admin');
        $file1['data'] = '';
        $file1['file'] = execute_query("SELECT file FROM tbl_uploads WHERE uniqid='$id'");
        $file1['hash'] = md5_file($file);
        $this->assertSame($json, $file1);

        // Create a customer with a note and a file uploaded previously
        $json = test_cli_helper('app/customers/insert', [
            'name' => 'The SaltOS project',
            'code' => '12345678X',
            'city' => 'Barcelona',
            'zip' => '08001',
            'addnotes' => 'Test note number one',
            'addfiles' => [$file1],
        ], '', '', 'admin');
        $this->assertSame($json['status'], 'ok');
        $this->assertSame(count($json), 2);
        $this->assertArrayHasKey('created_id', $json);

        // Store the created_id to be used in the modify and delete part
        $reg_id = $json['created_id'];
        $note_id = execute_query('SELECT id FROM app_customers_notes WHERE reg_id = ?', [$reg_id]);
        $file_id = execute_query('SELECT id FROM app_customers_files WHERE reg_id = ?', [$reg_id]);

        // Add a file to the tbl_uploads
        $id = get_unique_id_md5();
        $file = '../../utest/files/lorem.html';
        $app = 'app/test/file';
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

        // Modify the customer to remove the note and file and add a new note and file
        $json = test_cli_helper("app/customers/update/$reg_id", [
            'name' => 'The SaltOS project v2',
            'code' => '12345678Z',
            'addnotes' => 'Test note number two',
            'addfiles' => [$file2],
            'delnotes' => "$note_id, a, 0",
            'delfiles' => "$file_id, a, 0",
        ], '', '', 'admin');
        $this->assertSame($json['status'], 'ok');
        $this->assertSame(count($json), 2);
        $this->assertArrayHasKey('updated_id', $json);

        // Store the needed data to the next step
        $note_id = execute_query('SELECT id FROM app_customers_notes WHERE reg_id = ?', [$reg_id]);
        $file_id = execute_query('SELECT id FROM app_customers_files WHERE reg_id = ?', [$reg_id]);

        // Cover the lib/files.php functions
        files_cid('customers', $reg_id, $file_id);
        files_viewpdf('customers', $reg_id, $file_id);
        files_download('customers', $reg_id, $file_id);

        // Delete the customer with the note and file
        $json2 = test_cli_helper("app/customers/delete/$reg_id", '', '', '', 'admin');
        $this->assertSame($json2['status'], 'ok');
        $this->assertSame(count($json2), 2);
        $this->assertArrayHasKey('deleted_id', $json2);
    }

    #[testdox('notes functions')]
    /**
     * notes test
     *
     * This test performs some tests to validate the correctness
     * of the notes feature
     */
    public function test_notes(): void
    {
        $this->assertFalse(check_notes_old('dashboard', 'main'));
        $this->assertFalse(check_notes_old('dashboard', 'view'));
        $this->assertFalse(check_notes_old('configlog', 'view'));
        $this->assertTrue(check_notes_old('customers', 'view'));
        $this->assertFalse(check_notes_old('customers', 'view', 0));

        $this->assertFalse(check_notes_new('dashboard', 'main'));
        $this->assertFalse(check_notes_new('dashboard', 'edit'));
        $this->assertFalse(check_notes_new('configlog', 'edit'));
        $this->assertTrue(check_notes_new('customers', 'edit'));
    }

    #[testdox('fileslog functions')]
    /**
     * fileslog test
     *
     * This test performs some tests to validate the correctness
     * of the fileslog feature
     */
    public function test_fileslog(): void
    {
        ob_passthru('echo hola > data/logs/nada.log');
        ob_passthru('echo adios | gzip > data/logs/nada.1.log.gz');
        $json = test_cli_helper('app/fileslog/list/data', [
            'search' => '+nada +',
        ], '', '', 'admin');
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey(0, $json['data']);
        $this->assertArrayHasKey('id', $json['data'][0]);
        $id0 = $json['data'][0]['id'];
        $id1 = $json['data'][1]['id'];

        $json = test_cli_helper('app/fileslog/view/nada', '', '', '', 'admin');
        $this->assertArrayHasKey('error', $json);
        $this->assertSame($json['error']['text'], 'Permission denied');

        $json = test_cli_helper("app/fileslog/view/$id0", '', '', '', 'admin');
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('name', $json['data']);
        $this->assertArrayHasKey('size', $json['data']);
        $this->assertArrayHasKey('type', $json['data']);
        $this->assertArrayHasKey('data', $json['data']);
        $this->assertSame($json['data']['name'], 'nada.1.log.gz');
        $this->assertSame($json['data']['size'], '26 bytes');
        $this->assertSame($json['data']['type'], 'application/gzip');
        $this->assertSame($json['data']['data'], "adios\n");

        $json = test_cli_helper("app/fileslog/view/$id1", '', '', '', 'admin');
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('name', $json['data']);
        $this->assertArrayHasKey('size', $json['data']);
        $this->assertArrayHasKey('type', $json['data']);
        $this->assertArrayHasKey('data', $json['data']);
        $this->assertSame($json['data']['name'], 'nada.log');
        $this->assertSame($json['data']['size'], '5 bytes');
        $this->assertSame($json['data']['type'], 'text/plain');
        $this->assertSame($json['data']['data'], "hola\n");

        unlink('data/logs/nada.log');
        unlink('data/logs/nada.1.log.gz');

        $json = __files_view('nada.log');
        $this->assertArrayHasKey('status', $json);
        $this->assertArrayHasKey('text', $json);
        $this->assertArrayHasKey('code', $json);
        $this->assertSame($json['status'], 'ko');
        $this->assertSame($json['text'], 'File not found');
    }
}
