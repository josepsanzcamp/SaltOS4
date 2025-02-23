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

        set_config('dirs/tempdir2', null);
        $this->assertSame(get_directory('dirs/tempdir2'), null);

        $this->assertStringContainsString(getcwd() . '/data/temp/', get_temp_file());
        $this->assertSame(strlen(getcwd() . '/data/temp/') + 32 + 4, strlen(get_temp_file('tmp')));

        $this->assertStringContainsString(getcwd() . '/data/cache/', get_cache_file(''));
        $this->assertSame(strlen(getcwd() . '/data/cache/') + 32 + 4, strlen(get_cache_file([], 'tmp')));
        $this->assertStringEndsWith('.toml', get_cache_file(['nada' => 'nada.toml']));
        $this->assertStringEndsWith('.toml', get_cache_file(['nada' => 'nada.toml', 'nada2' => 112]));

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

        $json0 = url_get_contents('127.0.0.1/saltos/code4/api/?/auth/check');
        $json = url_get_contents('https://127.0.0.1/saltos/code4/api/?/auth/check');
        $this->assertSame($json0, $json);
        $json = json_decode($json, true);
        $this->assertSame($json['status'], 'ko');
        $this->assertSame(count($json), 3);
        $this->assertArrayHasKey('text', $json);

        $this->assertSame(extension('pepe.txt'), 'txt');
        $this->assertSame(extension('pepe.txt;'), 'txt');
        $this->assertSame(extension(serialize(['pepe.txt'])), 'txt');
        $this->assertSame(extension(json_encode(['pepe.txt'])), 'txt');

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
        $this->assertSame($file, file_with_min($file));
        unlink($file);

        $file2 = str_replace('.tmp', '.min.tmp', $file);
        file_put_contents($file2, '');
        $this->assertSame($file2, file_with_min($file));
        unlink($file2);

        $this->assertSame(false, file_get_contents_protected($file));
        $this->assertSame(false, file_get_contents_protected($file2));

        $errno = 0;
        $errstr = '';
        $fd = fsockopen_protected('127.0.0.1', 80, $errno, $errstr, null);
        $this->assertSame(is_resource($fd), true);

        $buffer = __url_get_contents('https://127.0.0.1nada/saltos/code4/api/?/auth/check');
        $this->assertSame($buffer, [
            'body' => '',
            'headers' => [],
            'cookies' => [],
            'code' => 0,
            'error' => 'error 6: Could not resolve host: 127.0.0.1nada',
        ]);

        $buffer = __url_get_contents('nada://127.0.0.1/saltos/code4/api/?/auth/check');
        $this->assertSame($buffer, [
            'body' => '',
            'headers' => [],
            'cookies' => [],
            'code' => 0,
            'error' => 'error 1: Protocol "nada" not supported or disabled in libcurl',
        ]);

        $buffer = __url_get_contents('https://127.0.0.1/saltos/code4/api/?/auth/check', [
            'method' => '',
        ]);
        $this->assertSame($buffer['code'], 400);
        $this->assertSame(strlen($buffer['body']) > 0, true);
        $this->assertStringContainsString('400 Bad Request', $buffer['body']);
        $this->assertStringContainsString('HTTP/1.1 400 Bad Request', array_keys($buffer['headers'])[0]);

        $buffer = __url_get_contents('https://127.0.0.1/saltos/code4/api/?/auth/check', [
            'method' => 'head',
        ]);
        $this->assertSame($buffer['code'], 200);
        $this->assertSame($buffer['body'], '');
        $this->assertSame(count($buffer['headers']) > 0, true);

        $buffer = __url_get_contents('https://127.0.0.1/saltos/code4/api/?/auth/check', [
            'cookies' => ['nada' => 'nada'],
            'method' => 'get',
            'values' => ['nada' => 'nada'],
            'headers' => [
                'nada' => 'nada',
                'referer' => 'https://127.0.0.1/saltos/code4/api/',
                'user-agent' => 'nada',
            ],
            'body' => 'nada',
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
        $this->assertStringContainsString('HTTP/1.1 401 Unauthorized', array_keys($buffer['headers'])[0]);
    }
}
