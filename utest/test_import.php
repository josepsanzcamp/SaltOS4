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
 * Test import
 *
 * This test performs some tests to validate the correctness
 * of the import functions
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
require_once __ROOT__ . 'php/lib/import.php';

/**
 * Main class of this unit test
 */
final class test_import extends TestCase
{
    #[testdox('import xml functions')]
    /**
     * import xml test
     *
     * This test performs some tests to validate the correctness
     * of the import functions
     */
    public function test_import_xml(): void
    {
        $rows = import_file([
            'file' => '../../utest/files/numbers.nada',
            'type' => 'xml',
        ]);
        $this->assertStringContainsString('not found', $rows);

        //~ $rows = import_file([
            //~ "file" => "../../utest/files/numbers.xml",
            //~ "type" => "xmlxml",
        //~ ]);
        //~ $this->assertStringContainsString("Unknown type", $rows);

        $data = "\xef\xbb\xbf" . file_get_contents('../../utest/files/numbers.xml');
        $file = get_cache_file($data, 'tmp');
        if (file_exists($file)) {
            unlink($file);
        }
        $rows = import_file([
            'data' => $data,
            'type' => 'xml',
        ]);
        $this->assertSame(is_array($rows), true);
        $this->assertSame(count($rows['rows']), 1000);
        $this->assertSame(array_keys($rows['rows']['row']), ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I']);

        $prefn = function ($args) {
            return 'nada';
        };
        $postfn = function ($args) {
            return $args;
        };
        $rows = import_file([
            'file' => '../../utest/files/numbers.xml',
            'type' => 'xml',
            'prefn' => $prefn,
            'postfn' => $postfn,
        ]);
        $this->assertSame($rows, 'nada');

        $prefn = function ($args) {
            return $args;
        };
        $postfn = function ($args) {
            return 'nada';
        };
        $rows = import_file([
            'file' => '../../utest/files/numbers.xml',
            'type' => 'xml',
            'prefn' => $prefn,
            'postfn' => $postfn,
        ]);
        $this->assertSame($rows, 'nada');
    }

    #[testdox('import csv functions')]
    /**
     * import csv test
     *
     * This test performs some tests to validate the correctness
     * of the import functions
     */
    public function test_import_csv(): void
    {
        $rows = import_file([
            'file' => '../../utest/files/numbers.csv',
            'type' => 'csv',
        ]);
        $this->assertSame(is_array($rows), true);
        $this->assertSame(count($rows), 1000);
        $this->assertSame(array_keys($rows[0]), ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I']);
    }

    #[testdox('import xlsx functions')]
    /**
     * import xlsx test
     *
     * This test performs some tests to validate the correctness
     * of the import functions
     */
    public function test_import_xlsx(): void
    {
        $rows = import_file([
            'file' => '../../utest/files/numbers.xlsx',
            'type' => 'xlsx',
            'sheet' => 'example',
        ]);
        $this->assertSame(is_array($rows), true);
        $this->assertSame(count($rows), 100);
        $this->assertSame(array_keys($rows[0]), ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I']);
        $this->assertSame($rows[1]['A'], 2);
        $this->assertSame($rows[99]['I'], '2024-02-01');

        $file = get_directory('dirs/cachedir') . get_unique_id_md5() . '.xlsx';
        copy('../../utest/files/bigsize.xlsx', $file);
        chmod_protected($file, 0666);
        $rows = import_file([
            'file' => $file,
            'type' => 'xlsx',
            'sheet' => 'example',
        ]);
        $this->assertSame(is_array($rows), true);
        $this->assertSame(count($rows), 100);

        $rows = import_file([
            'file' => '../../utest/files/numbers.xlsx',
            'type' => 'xlsx',
            'sheet' => 'a',
        ]);
        $this->assertStringContainsString('not found', $rows);

        $rows = import_file([
            'file' => '../../utest/files/numbers.xlsx',
            'type' => 'xlsx',
            'sheet' => '1',
        ]);
        $this->assertStringContainsString('not found', $rows);

        $file = get_cache_file('../../utest/files/bigsize.xlsx', 'csv');
        if (file_exists($file)) {
            unlink($file);
        }
        $rows = import_file([
            'file' => '../../utest/files/bigsize.xlsx',
            'type' => 'xlsx',
            'sheet' => 'example',
        ]);
        $this->assertSame(is_array($rows), true);
        $this->assertSame(count($rows), 100);
        $this->assertSame(array_keys($rows[0]), ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I']);
    }

    #[testdox('import xls functions')]
    /**
     * import xls test
     *
     * This test performs some tests to validate the correctness
     * of the import functions
     */
    public function test_import_xls(): void
    {
        $rows = import_file([
            'file' => '../../utest/files/numbers.xls',
            'type' => 'xls',
        ]);
        $this->assertSame(is_array($rows), true);
        $this->assertSame(count($rows), 100);
        $this->assertSame(array_keys($rows[0]), ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I']);
    }

    #[testdox('import ods functions')]
    /**
     * import ods test
     *
     * This test performs some tests to validate the correctness
     * of the import functions
     */
    public function test_import_ods(): void
    {
        $rows = import_file([
            'file' => '../../utest/files/numbers.ods',
            'type' => 'ods',
        ]);
        $this->assertSame(is_array($rows), true);
        $this->assertSame(count($rows), 100);
        $this->assertSame(array_keys($rows[0]), ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I']);
    }

    #[testdox('import bytes functions')]
    /**
     * import bytes test
     *
     * This test performs some tests to validate the correctness
     * of the import functions
     */
    public function test_import_bytes(): void
    {
        $map = [
            ['A', 0, 10],
            ['B', 10, 10],
            ['C', 20, 10],
            ['D', 30, 10],
            ['E', 40, 10],
            ['F', 50, 10],
            ['G', 60, 10],
            ['H', 70, 10],
            ['I', 80, 10],
        ];
        foreach ($map as $key => $val) {
            $map[$key] = implode(';', $val);
        }
        $map = array_map('strval', $map);
        $map = implode("\n", $map);

        $rows = import_file([
            'file' => '../../utest/files/numbers.bytes',
            'type' => 'bytes',
            'map' => $map,
        ]);
        $this->assertSame(is_array($rows), true);
        $this->assertSame(count($rows), 1000);
        $this->assertSame(array_keys($rows[0]), ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I']);

        $rows = import_file([
            'file' => '../../utest/files/numbers.bytes',
            'type' => 'bytes',
            'map' => $map,
            'nomb' => true,
        ]);
        $this->assertSame(is_array($rows), true);
        $this->assertSame(count($rows), 1000);
        $this->assertSame(array_keys($rows[0]), ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I']);
    }

    #[testdox('import edi functions')]
    /**
     * import edi test
     *
     * This test performs some tests to validate the correctness
     * of the import functions
     */
    public function test_import_edi(): void
    {
        $rows = import_file([
            'file' => '../../utest/files/numbers.edi',
            'type' => 'edi',
        ]);
        $this->assertSame(is_array($rows), true);
        $this->assertSame(count($rows), 1000);
        $this->assertSame(array_keys($rows[0]), ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I']);
    }

    #[testdox('import json functions')]
    /**
     * import json test
     *
     * This test performs some tests to validate the correctness
     * of the import functions
     */
    public function test_import_json(): void
    {
        $rows = import_file([
            'file' => '../../utest/files/numbers.json',
            'type' => 'json',
        ]);
        $this->assertSame(is_array($rows), true);
        $this->assertSame(count($rows), 1000);
        $this->assertSame(array_keys($rows[0]), ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I']);

        $rows = import_file([
            'data' => 'a',
            'type' => 'json',
        ]);
        $this->assertStringContainsString('Syntax error', $rows);
    }

    #[testdox('import helper functions')]
    /**
     * import helper test
     *
     * This test performs some tests to validate the correctness
     * of the import functions
     */
    public function test_import_helper(): void
    {
        $array = [['a', 'b', 'c'], ['d', 'e', 'f']];
        $this->assertSame(__import_check_real_matrix($array), true);

        $array = ['a'];
        $this->assertSame(__import_check_real_matrix($array), false);

        $array = [[['a']]];
        $this->assertSame(__import_check_real_matrix($array), false);

        //~ $array = "";
        //~ $this->assertSame(__import_removevoid($array), "");

        $array = [];
        $this->assertSame(__import_removevoid($array), []);

        $array = [
            ['', '1', '5', ''],
            ['', '2', '6', ''],
            ['', '3', '7', ''],
            ['', '4', '8', ''],
        ];
        $this->assertSame(__import_removevoid($array), [
            ['1', '5'],
            ['2', '6'],
            ['3', '7'],
            ['4', '8'],
        ]);

        $array = [
            ['', '', '', ''],
            ['1', '2', '3', '4'],
            ['5', '6', '7', '8'],
            ['', '', '', ''],
        ];
        $this->assertSame(__import_removevoid($array), [
            ['1', '2', '3', '4'],
            ['5', '6', '7', '8'],
        ]);

        //~ $array = "";
        //~ $this->assertSame(__import_array2tree($array, "", false, false), "");

        $array = [];
        $this->assertSame(__import_array2tree($array, '', false, false), []);

        $array = [
            ['1', '2', '3', '4'],
            ['5', '6', '7', '8'],
            ['9', 'a', 'b', 'c'],
            ['d', 'e', 'f', 'g'],
        ];
        $this->assertIsArray(__import_array2tree($array, '', true, false));
        $this->assertIsArray(__import_array2tree($array, ['0,B,Field', 'C,D', ''], true, true));

        $array = [
            ['1', '2'],
            ['3'],
        ];
        $this->assertIsArray(__import_array2tree($array, ['0,1,2', '', ''], false, false));

        $this->assertFalse(__import_isname('0'));
    }

    #[testdox('external exec')]
    /**
     * external exec
     *
     * This test performs some tests to validate the correctness
     * of the external exec
     */
    public function test_import_external(): void
    {
        test_external_exec('php/import1.php', 'phperror.log', 'unknown file');
        test_external_exec('php/import2.php', 'phperror.log', 'unknown type');
        test_external_exec('php/import3.php', 'phperror.log', 'unknown type nada for file');
    }
}
