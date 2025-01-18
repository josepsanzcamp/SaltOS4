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
 * Test gc
 *
 * This test performs some tests to validate the correctness
 * of the gc functions
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
require_once 'php/lib/gc.php';
require_once 'php/lib/upload.php';
require_once 'php/lib/trash.php';

/**
 * Main class of this unit test
 */
final class test_gc extends TestCase
{
    #[testdox('gc_exec function')]
    /**
     * gc exec
     *
     * This test performs some tests to validate the correctness
     * of the gc functions
     */
    public function test_gc_exec(): void
    {
        $file = get_temp_file();
        $this->assertFileDoesNotExist($file);
        file_put_contents($file, '');
        $this->assertFileExists($file);

        $old = get_config('server/cachetimeout');
        set_config('server/cachetimeout', 0);
        $this->assertSame(get_config('server/cachetimeout'), 0);

        sleep(1); // the internally filemtime used have one second of resolution
        gc_exec();
        $this->assertFileDoesNotExist($file);
        $this->assertSame(count(glob('data/cache/*')), 0);
        $this->assertSame(count(glob('data/temp/*')), 0);
        $this->assertSame(count(glob('data/upload/*')), 0);

        set_config('server/cachetimeout', $old);
        $this->assertSame(get_config('server/cachetimeout'), $old);

        test_external_exec('php/gc1.php', 'phperror.log', 'internal error');

        $file = 'data/logs/phperror.log';
        $this->assertFileDoesNotExist($file);

        $json = test_web_helper('gc', [], '', '');
        $this->assertArrayHasKey('error', $json);
        $this->assertFileExists($file);
        $this->assertTrue(words_exists('permission denied', file_get_contents($file)));
        unlink($file);

        $json = test_cli_helper('gc', [], '', '', '');
        $this->assertArrayHasKey('gc_upload', $json);
        $this->assertArrayHasKey('gc_trash', $json);
        $this->assertArrayHasKey('gc_exec', $json);
    }

    #[testdox('gc_upload function')]
    /**
     * gc upload
     *
     * This test performs some tests to validate the correctness
     * of the gc functions
     */
    public function test_gc_upload(): void
    {
        $this->assertSame(count(glob('data/upload/*')), 0);

        $type = 'text/plain';
        $data = 'Hello world';
        $file1 = [
            'id' => 'idtest',
            'app' => 'apptest',
            'name' => 'file.txt',
            'type' => $type,
            'size' => strlen($data),
            'data' => mime_inline($type, $data),
        ];
        $file2 = add_upload_file($file1);
        $this->assertSame(count(glob('data/upload/*')), 1);

        $id = check_upload_file([
            'uniqid' => 'idtest',
            'app' => 'apptest',
            'name' => 'file.txt',
            'type' => $type,
            'size' => strlen($data),
        ]);
        $this->assertTrue($id > 0);

        $file1['data'] = '';
        $file1['file'] = execute_query("SELECT file FROM tbl_uploads WHERE id='$id'");
        $file1['hash'] = execute_query("SELECT hash FROM tbl_uploads WHERE id='$id'");
        $this->assertSame($file2, $file1);

        $query = make_update_query('tbl_uploads', [
            'datetime' => '0000-00-00 00:00:00',
        ], [
            'id' => $id,
        ]);
        db_query($query);

        gc_upload();
        $this->assertSame(count(glob('data/upload/*')), 0);
    }

    #[testdox('gc_trash function')]
    /**
     * gc upload
     *
     * This test performs some tests to validate the correctness
     * of the gc functions
     */
    public function test_gc_trash(): void
    {
        $query = make_update_query('tbl_trash', [
            'datetime' => '0000-00-00 00:00:00',
        ], []);
        db_query($query);

        gc_trash();
        $this->assertSame(count(glob('data/trash/*')), 0);
    }
}
