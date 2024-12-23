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
 * Test file
 *
 * This test performs some tests to validate the correctness
 * of the file functions
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
require_once 'php/lib/upload.php';

/**
 * Main class of this unit test
 */
final class test_file extends TestCase
{
    #[testdox('file functions')]
    /**
     * file test
     *
     * This test performs some tests to validate the correctness
     * of the file functions
     */
    public function test_file(): void
    {
        $this->assertSame(get_directory('dirs/tempdir'), getcwd() . '/data/temp/');

        $this->assertSame(get_directory('dirs/tempdir2'), null);

        set_config('dirs/tempdir2', ['#attr' => ['eval' => 'true'], 'value' => "getcwd().'/data/temp/'"]);
        $this->assertSame(get_directory('dirs/tempdir2'), getcwd() . '/data/temp/');

        $this->assertStringContainsString(getcwd() . '/data/temp/', get_temp_file());
        $this->assertSame(strlen(getcwd() . '/data/temp/') + 32 + 4, strlen(get_temp_file('tmp')));

        $this->assertStringContainsString(getcwd() . '/data/cache/', get_cache_file(''));
        $this->assertSame(strlen(getcwd() . '/data/cache/') + 32 + 4, strlen(get_cache_file([], 'tmp')));

        $file1 = get_temp_file();
        file_put_contents($file1, '');
        $file2 = get_temp_file();
        file_put_contents($file2, '');
        sleep(1); // the internally filemtime used have one second of resolution
        $file3 = get_temp_file();
        $this->assertSame(cache_exists($file3, [$file1, $file2]), false);
        file_put_contents($file3, '');
        $this->assertSame(cache_exists($file3, $file1), true);
        $this->assertSame(cache_exists($file1, ['nada']), false);
        $this->assertSame(cache_exists($file3, [$file1, $file2]), true);
        $this->assertSame(cache_exists($file1, [$file3, $file2]), false);
        unlink($file1);
        unlink($file2);
        unlink($file3);

        $json0 = url_get_contents('127.0.0.1/saltos/code4/api/?auth/check');
        $json = url_get_contents('https://127.0.0.1/saltos/code4/api/?auth/check');
        $this->assertSame($json0, $json);
        $json = json_decode($json, true);
        $this->assertSame($json['status'], 'ko');
        $this->assertSame(count($json), 3);
        $this->assertArrayHasKey('text', $json);

        $this->assertSame(extension('pepe.txt'), 'txt');

        $this->assertSame(encode_bad_chars_file('Hola Mundo.txt'), 'hola_mundo.txt');

        $this->assertSame(realpath_protected(getcwd() . '/pepe.txt'), getcwd() . '/pepe.txt');

        $this->assertSame(getcwd_protected(), getcwd());
        $oldcwd = getcwd();
        chdir('/');
        $this->assertSame(getcwd_protected(), dirname(get_server('SCRIPT_FILENAME')));
        chdir($oldcwd);

        $this->assertSame(is_array(glob_protected('*')), true);
        $this->assertSame(count(glob_protected('*')) > 0, true);
        $this->assertSame(is_array(glob_protected('nada')), true);
        $this->assertSame(count(glob_protected('nada')) == 0, true);

        $file = get_temp_file();
        file_put_contents($file, '');
        $this->assertSame(chmod_protected($file, 0666), true);
        $this->assertSame(chmod_protected($file, 0666), false);
        unlink($file);

        $file = get_directory('dirs/cachedir') .
            ob_passthru("ls -l data/cache | grep www-data | tr ' ' '\n' | tail -1");
        $this->assertSame(chmod_protected($file, 0664), false);

        $file = get_temp_file();
        file_put_contents($file, '');
        ob_passthru("sudo chown root:root $file");
        ob_passthru("sudo chmod 0777 $file");
        $this->assertSame(chmod_protected($file, 0664), false);
        unlink($file);

        $file = get_temp_file();
        file_put_contents($file, '');
        $this->assertSame(strlen($file) + 1 + 32, strlen(file_with_hash($file)));
        unlink($file);

        $errno = 0;
        $errstr = '';
        $fd = fsockopen_protected('127.0.0.1', 80, $errno, $errstr, null);
        $this->assertSame(is_resource($fd), true);

        $buffer = __url_get_contents('https://127.0.0.1nada/saltos/code4/api/?auth/check');
        $this->assertSame($buffer, [
            'body' => '',
            'headers' => [],
            'cookies' => [],
            'code' => 0,
            'error' => 'error 6: Could not resolve host: 127.0.0.1nada',
        ]);

        $buffer = __url_get_contents('nada://127.0.0.1/saltos/code4/api/?auth/check');
        $this->assertSame($buffer, [
            'body' => '',
            'headers' => [],
            'cookies' => [],
            'code' => 0,
            'error' => 'error 1: Protocol "nada" not supported or disabled in libcurl',
        ]);

        $buffer = __url_get_contents('https://127.0.0.1/saltos/code4/api/?auth/check', [
            'method' => '',
        ]);
        $this->assertSame($buffer['code'], 400);
        $this->assertSame(strlen($buffer['body']) > 0, true);
        $this->assertStringContainsString('400 Bad Request', $buffer['body']);
        $this->assertArrayHasKey('http/1.1 400 bad request', $buffer['headers']);

        $buffer = __url_get_contents('https://127.0.0.1/saltos/code4/api/?auth/check', [
            'method' => 'head',
        ]);
        $this->assertSame($buffer['code'], 200);
        $this->assertSame($buffer['body'], '');
        $this->assertSame(count($buffer['headers']) > 0, true);

        $buffer = __url_get_contents('https://127.0.0.1/saltos/code4/api/?auth/check', [
            'cookies' => ['nada' => 'nada'],
            'method' => 'get',
            'values' => ['nada' => 'nada'],
            'referer' => 'https://127.0.0.1/saltos/code4/api/',
            'headers' => ['nada' => 'nada'],
            'body' => 'nada',
            'user_agent' => 'nada',
        ]);
        $this->assertSame($buffer['code'], 200);
        $this->assertSame(is_array($buffer), true);
        $this->assertSame(strlen($buffer['body']) > 0, true);

        $buffer = __url_get_contents('http://127.0.0.1:631/admin/');
        $temp = $buffer['cookies']['org.cups.sid'];
        $cookies = $buffer['cookies'];
        $buffer = __url_get_contents('http://127.0.0.1:631/admin/', [
            'cookies' => $cookies,
            'method' => 'post',
            'values' => [
                'org.cups.sid' => $temp,
                'OP' => 'add-printer',
            ],
        ]);
        $this->assertSame($buffer['code'], 401);
        $this->assertSame(strlen($buffer['body']) > 0, true);
        $this->assertStringContainsString('Unauthorized', $buffer['body']);
        $this->assertArrayHasKey('http/1.1 401 unauthorized', $buffer['headers']);
    }

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
    #[testdox('addfiles action')]
    /**
     * Addfiles
     *
     * This function execute the addfiles rest request, and must to get the
     * json with the correct data
     */
    public function test_addfiles(array $json): array
    {
        $json2 = test_web_helper('upload/addfile', [], '', '');
        $this->assertArrayHasKey('error', $json2);
        $this->assertSame($json2['error']['text'], 'Permission denied');

        $json2 = test_web_helper('upload/addfile', [], $json['token'], '');
        $this->assertArrayHasKey('error', $json2);
        $this->assertSame($json2['error']['text'], 'file not found');

        $json2 = test_web_helper('upload/addfile', [
            'error' => 'nada',
        ], $json['token'], '');
        $this->assertArrayHasKey('error', $json2);
        $this->assertSame($json2['error']['text'], 'Missing id, app, name, size, type, data, file, hash');

        $json2 = test_web_helper('upload/addfile', [
            'id' => '',
            'app' => '',
            'name' => '',
            'size' => '',
            'type' => '',
            'data' => '',
            'error' => 'nada',
            'file' => '',
            'hash' => '',
        ], $json['token'], '');
        $this->assertArrayHasKey('error', $json2);
        $this->assertSame($json2['error']['text'], 'nada');

        $count1 = count(glob('data/upload/*'));

        $id = get_unique_id_md5();
        $file = '../../utest/files/lorem.html';
        $app = 'app/test/file';
        $name = basename($file);
        $size = filesize($file);
        $type = saltos_content_type($file);
        $data = mime_inline($type, file_get_contents($file));
        $files = [
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
        $json2 = test_web_helper('upload/addfile', $files, $json['token'], '');
        $files['data'] = '';
        $files['file'] = execute_query("SELECT file FROM tbl_uploads WHERE uniqid='$id'");
        $files['hash'] = md5_file($file);
        $this->assertSame($json2, $files);

        $count2 = count(glob('data/upload/*'));
        $this->assertSame($count1 + 1, $count2);

        // To cover the first return of the function
        $type = 'text/plain';
        $data = 'Hello world';
        $file1 = [
            'id' => 'idtest',
            'app' => 'apptest',
            'name' => 'file.txt',
            'type' => 'nada',
            'size' => strlen($data),
            'data' => mime_inline($type, $data),
        ];
        $file2 = add_file($file1);
        $file1['data'] = '';
        $this->assertSame($file2, $file1);

        // To cover the second return of the function
        $type = 'text/plain';
        $data = 'Hello world';
        $file1 = [
            'id' => 'idtest',
            'app' => 'apptest',
            'name' => 'file.txt',
            'type' => $type,
            'size' => 0,
            'data' => mime_inline($type, $data),
        ];
        $file2 = add_file($file1);
        $file1['data'] = '';
        $this->assertSame($file2, $file1);

        return [
            'token' => $json['token'],
            'files' => $files,
        ];
    }

    #[Depends('test_addfiles')]
    #[testdox('delfiles action')]
    /**
     * Delfiles
     *
     * This function execute the delfiles rest request, and must to get the
     * json with the correct data
     */
    public function test_delfiles(array $json): void
    {
        $json2 = test_web_helper('upload/delfile', [], '', '');
        $this->assertArrayHasKey('error', $json2);

        $json2 = test_web_helper('upload/delfile', [], $json['token'], '');
        $this->assertArrayHasKey('error', $json2);

        $json2 = test_web_helper('upload/delfile', [
            'error' => 'nada',
        ], $json['token'], '');
        $this->assertArrayHasKey('error', $json2);
        $this->assertSame($json2['error']['text'], 'Missing id, app, name, size, type, data, file, hash');

        $json2 = test_web_helper('upload/delfile', [
            'id' => '',
            'app' => '',
            'name' => '',
            'size' => '',
            'type' => '',
            'data' => '',
            'error' => 'nada',
            'file' => '',
            'hash' => '',
        ], $json['token'], '');
        $this->assertArrayHasKey('error', $json2);
        $this->assertSame($json2['error']['text'], 'nada');

        // To cover the first return of the function
        $file1 = [
            'id' => '',
            'app' => '',
            'name' => '',
            'type' => '',
            'size' => '',
            'file' => '',
            'hash' => '',
        ];
        $file2 = del_file($file1);
        $this->assertSame($file2, $file1);

        // Search the good data to be used in the next steps of this utest
        $query = 'SELECT * FROM tbl_uploads WHERE ' . make_where_query('tbl_uploads', [
            'app' => 'app/test/file',
        ]) . ' LIMIT 1';
        $file0 = execute_query($query);

        // Change the filename to break the filename integrity
        // Note that the user_id is set to zero
        $query = make_update_query('tbl_uploads', [
           'file' => 'pepe/pepe.txt',
           'user_id' => 0,
        ], [
            'id' => $file0['id'],
        ]);
        db_query($query);

        $file1 = [
            'id' => $file0['uniqid'],
            'app' => $file0['app'],
            'name' => $file0['name'],
            'type' => $file0['type'],
            'size' => $file0['size'],
            'file' => 'pepe/pepe.txt',
            'hash' => $file0['hash'],
        ];
        $file2 = del_file($file1);
        $this->assertSame($file2, $file1);

        // Change the filename to break the file_exists integrity
        $query = make_update_query('tbl_uploads', [
           'file' => 'pepe_pepe.txt',
        ], [
            'id' => $file0['id'],
        ]);
        db_query($query);

        $file1 = [
            'id' => $file0['uniqid'],
            'app' => $file0['app'],
            'name' => $file0['name'],
            'type' => $file0['type'],
            'size' => $file0['size'],
            'file' => 'pepe_pepe.txt',
            'hash' => $file0['hash'],
        ];
        $file2 = del_file($file1);
        $this->assertSame($file2, $file1);

        // Change to break the filesize integrity
        $query = make_update_query('tbl_uploads', [
           'file' => $file0['file'],
           'size' => -1,
        ], [
            'id' => $file0['id'],
        ]);
        db_query($query);

        $file1 = [
            'id' => $file0['uniqid'],
            'app' => $file0['app'],
            'name' => $file0['name'],
            'type' => $file0['type'],
            'size' => -1,
            'file' => $file0['file'],
            'hash' => $file0['hash'],
        ];
        $file2 = del_file($file1);
        $this->assertSame($file2, $file1);

        // Change to break the hash integrity
        $query = make_update_query('tbl_uploads', [
           'size' => $file0['size'],
           'hash' => 'nada',
        ], [
            'id' => $file0['id'],
        ]);
        db_query($query);

        $file1 = [
            'id' => $file0['uniqid'],
            'app' => $file0['app'],
            'name' => $file0['name'],
            'type' => $file0['type'],
            'size' => $file0['size'],
            'file' => $file0['file'],
            'hash' => 'nada',
        ];
        $file2 = del_file($file1);
        $this->assertSame($file2, $file1);

        // Restore all previous changed data
        $query = make_update_query('tbl_uploads', [
           'hash' => $file0['hash'],
           'user_id' => $file0['user_id'],
        ], [
            'id' => $file0['id'],
        ]);
        db_query($query);

        // Continue
        $count1 = count(glob('data/upload/*'));

        $json2 = test_web_helper('upload/delfile', $json['files'], $json['token'], '');
        $json['files']['file'] = '';
        $json['files']['hash'] = '';
        $this->assertSame($json2, $json['files']);

        $count2 = count(glob('data/upload/*'));
        $this->assertSame($count1, $count2 + 1);

        // Check for the unknown action
        $file = 'data/logs/phperror.log';
        $this->assertFileDoesNotExist($file);

        $json2 = test_web_helper('upload/nada', $json['files'], $json['token'], '');
        $this->assertArrayHasKey('error', $json2);
        $this->assertFileExists($file);
        $this->assertTrue(words_exists('unknown action nada', file_get_contents($file)));
        unlink($file);
    }
}
