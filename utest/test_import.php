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
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\DependsExternal;

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
            "file" => "../../utest/files/example.xml",
            "type" => "xml",
        ]);
        $this->assertSame(is_array($rows), true);
        $this->assertSame(count($rows["rows"]), 1000);
        $this->assertSame(array_keys($rows["rows"]["row"]), ["A", "B", "C", "D", "E", "F", "G", "H", "I"]);
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
            "file" => "../../utest/files/example.csv",
            "type" => "csv",
        ]);
        $this->assertSame(is_array($rows), true);
        $this->assertSame(count($rows), 1000);
        $this->assertSame(array_keys($rows[0]), ["A", "B", "C", "D", "E", "F", "G", "H", "I"]);
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
            "file" => "../../utest/files/example.xlsx",
            "type" => "xlsx",
        ]);
        $this->assertSame(is_array($rows), true);
        $this->assertSame(count($rows), 1000);
        $this->assertSame(array_keys($rows[0]), ["A", "B", "C", "D", "E", "F", "G", "H", "I"]);
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
            "file" => "../../utest/files/example.xls",
            "type" => "xls",
        ]);
        $this->assertSame(is_array($rows), true);
        $this->assertSame(count($rows), 1000);
        $this->assertSame(array_keys($rows[0]), ["A", "B", "C", "D", "E", "F", "G", "H", "I"]);
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
            "file" => "../../utest/files/example.ods",
            "type" => "ods",
        ]);
        $this->assertSame(is_array($rows), true);
        $this->assertSame(count($rows), 1000);
        $this->assertSame(array_keys($rows[0]), ["A", "B", "C", "D", "E", "F", "G", "H", "I"]);
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
        $rows = import_file([
            "file" => "../../utest/files/example.bytes",
            "type" => "bytes",
            "map" => [
                ["A",0,10],
                ["B",10,10],
                ["C",20,10],
                ["D",30,10],
                ["E",40,10],
                ["F",50,10],
                ["G",60,10],
                ["H",70,10],
                ["I",80,10],
            ],
        ]);
        $this->assertSame(is_array($rows), true);
        $this->assertSame(count($rows), 1000);
        $this->assertSame(array_keys($rows[0]), ["A", "B", "C", "D", "E", "F", "G", "H", "I"]);
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
            "file" => "../../utest/files/example.edi",
            "type" => "edi",
        ]);
        $this->assertSame(is_array($rows), true);
        $this->assertSame(count($rows), 1000);
        $this->assertSame(array_keys($rows[0]), ["A", "B", "C", "D", "E", "F", "G", "H", "I"]);
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
            "file" => "../../utest/files/example.json",
            "type" => "json",
        ]);
        $this->assertSame(is_array($rows), true);
        $this->assertSame(count($rows), 1000);
        $this->assertSame(array_keys($rows[0]), ["A", "B", "C", "D", "E", "F", "G", "H", "I"]);
    }
}
