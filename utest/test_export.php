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
 * Test export
 *
 * This test performs some tests to validate the correctness
 * of the export functions
 */

/**
 * Importing namespaces
 */
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\DependsExternal;

/**
 * Loading helper function
 *
 * This file contains the needed function used by the unit tests
 */
require_once "lib/mimelib.php";

/**
 * Main class of this unit test
 */
final class test_export extends TestCase
{
    /**
     * TODO
     *
     * TODO
     */
    private function get_data($nohead, $length): array
    {
        $rows = import_file([
            "file" => "../../utest/files/example.csv",
            "type" => "csv",
            "nohead" => $nohead,
        ]);
        $this->assertSame(is_array($rows), true);
        $rows = array_slice($rows, 0, $length);
        return $rows;
    }

    #[testdox('export xml functions')]
    /**
     * export xml test
     *
     * This test performs some tests to validate the correctness
     * of the export functions
     */
    public function test_export_xml(): void
    {
        $data = $this->get_data(false, 1000);
        foreach ($data as $key => $val) {
            $data["row#$key"] = $val;
            unset($data[$key]);
        }
        $data = ["rows" => $data];
        $buffer = export_file([
            "type" => "xml",
            "data" => $data,
        ]);
        $this->assertSame(is_string($buffer), true);
        $this->assertStringContainsString("XML 1.0 document", get_mime($buffer));
        $buffer = explode("\n", $buffer);
        // Notes: *11 => 9 columns + <row> + </row>
        // Notes: +4 => xml_header + <rows> + </rows> + eof
        $this->assertSame(count($buffer), 1000 * 11 + 4);
    }

    #[testdox('export csv functions')]
    /**
     * export csv test
     *
     * This test performs some tests to validate the correctness
     * of the export functions
     */
    public function test_export_csv(): void
    {
        $data = $this->get_data(true, 1001);
        $buffer = export_file([
            "type" => "csv",
            "data" => $data,
        ]);
        $this->assertSame(is_string($buffer), true);
        $this->assertStringContainsString("ASCII text", get_mime($buffer));
        $buffer = explode("\n", $buffer);
        $this->assertSame(count($buffer), 1001);
        $buffer[0] = explode(";", $buffer[0]);
        $this->assertSame($buffer[0], ["A", "B", "C", "D", "E", "F", "G", "H", "I"]);
    }

    #[testdox('export xlsx functions')]
    /**
     * export xlsx test
     *
     * This test performs some tests to validate the correctness
     * of the export functions
     */
    public function test_export_xlsx(): void
    {
        $data = $this->get_data(true, 100);
        $buffer = export_file([
            "type" => "xlsx",
            "data" => $data,
        ]);
        $this->assertSame(is_string($buffer), true);
        $this->assertStringContainsString("Microsoft Excel 2007+", get_mime($buffer));
    }

    #[testdox('export xls functions')]
    /**
     * export xls test
     *
     * This test performs some tests to validate the correctness
     * of the export functions
     */
    public function test_export_xls(): void
    {
        $data = $this->get_data(true, 100);
        $buffer = export_file([
            "type" => "xls",
            "data" => $data,
        ]);
        $this->assertSame(is_string($buffer), true);
        $this->assertStringContainsString("Composite Document File V2 Document", get_mime($buffer));
    }

    #[testdox('export ods functions')]
    /**
     * export ods test
     *
     * This test performs some tests to validate the correctness
     * of the export functions
     */
    public function test_export_ods(): void
    {
        $data = $this->get_data(true, 100);
        $buffer = export_file([
            "type" => "ods",
            "data" => $data,
        ]);
        $this->assertSame(is_string($buffer), true);
        $this->assertStringContainsString("Zip archive data", get_mime($buffer));
    }

    //~ #[testdox('export bytes functions')]
    //~ /**
     //~ * export bytes test
     //~ *
     //~ * This test performs some tests to validate the correctness
     //~ * of the export functions
     //~ */
    //~ public function test_export_bytes(): void
    //~ {
        //~ $this->markTestSkipped("NOT IMPLEMENTED");
    //~ }

    #[testdox('export edi functions')]
    /**
     * export edi test
     *
     * This test performs some tests to validate the correctness
     * of the export functions
     */
    public function test_export_edi(): void
    {
        $data = $this->get_data(true, 1001);
        $buffer = export_file([
            "type" => "edi",
            "data" => $data,
        ]);
        $this->assertSame(is_string($buffer), true);
        $this->assertStringContainsString("ASCII text", get_mime($buffer));
        $buffer = explode("\n", $buffer);
        $this->assertSame(count($buffer), 1001);
        $buffer[0] = explode("+", str_replace("'", "", $buffer[0]));
        $this->assertSame($buffer[0], ["A", "B", "C", "D", "E", "F", "G", "H", "I"]);
    }

    #[testdox('export json functions')]
    /**
     * export json test
     *
     * This test performs some tests to validate the correctness
     * of the export functions
     */
    public function test_export_json(): void
    {
        $data = $this->get_data(false, 1000);
        $buffer = export_file([
            "type" => "json",
            "data" => $data,
        ]);
        $this->assertSame(is_string($buffer), true);
        $this->assertStringContainsString("JSON text data", get_mime($buffer));
    }

    #[testdox('tree2array functions')]
    /**
     * tree2array test
     *
     * This test performs some tests to validate the correctness
     * of the tree2array function
     */
    public function test_export_tree2array(): void
    {
        $array = [
            [
                "row" => [
                    "a" => "1",
                    "b" => "2",
                    "c" => "3",
                ],
                "rows" => [
                    ["d" => "4", "e" => "5", "f" => "6"],
                    ["d" => "7", "e" => "8", "f" => "9"],
                ],
            ],
            [
                "row" => [
                    "a" => "4",
                    "b" => "5",
                    "c" => "6",
                ],
                "rows" => [
                    ["d" => "6", "e" => "5", "f" => "4"],
                    ["d" => "9", "e" => "8", "f" => "7"],
                ],
            ],
        ];
        $array = __export_tree2array($array);
        $this->assertSame(count($array), 4);
        foreach ($array as $key => $val) {
            $this->assertSame(count($val), 6);
            $this->assertSame(array_keys($val), ["a", "b", "c", "d", "e", "f"]);
        }
    }

    #[testdox('getkeys functions')]
    /**
     * getkeys test
     *
     * This test performs some tests to validate the correctness
     * of the getkeys function
     */
    public function test_export_getkeys(): void
    {
        $array = [
            [
                "row" => [
                    "a" => "1",
                    "b" => "2",
                    "c" => "3",
                ],
                "rows" => [
                    ["d" => "4", "e" => "5", "f" => "6"],
                    ["d" => "7", "e" => "8", "f" => "9"],
                ],
            ],
            [
                "row" => [
                    "a" => "4",
                    "b" => "5",
                    "c" => "6",
                ],
                "rows" => [
                    ["d" => "6", "e" => "5", "f" => "4"],
                    ["d" => "9", "e" => "8", "f" => "7"],
                ],
            ],
        ];
        $array = __export_getkeys($array);
        $this->assertSame(count($array), 6);
        $this->assertSame($array, ["a", "b", "c", "d", "e", "f"]);
    }
}
