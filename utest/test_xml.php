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
// phpcs:disable Generic.Files.LineLength

/**
 * Test XML
 *
 * This test performs some tests to validate the correctness
 * of the xml related functions
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
require_once "lib/utestlib.php";

/**
 * Main class of this unit test
 */
final class test_xml extends TestCase
{
    #[testdox('XML functions')]
    /**
     * XML test
     *
     * This function performs some tests to validate the correctness
     * of the xml related functions
     */
    public function test_xml(): void
    {
        $xml = '<a x="1"><b y="2"/><c z="3">d</c></a>';
        $this->assertSame(array2xml(xml2array($xml)), $xml);

        $this->assertSame(__array2xml_check_node_name("asd"), true);
        $this->assertSame(__array2xml_check_node_name("\asd"), false);

        $this->assertSame(__array2xml_check_node_attr("asd"), true);
        $this->assertSame(__array2xml_check_node_attr("\asd"), false);

        $array = ["a" => "&"];
        $this->assertStringContainsString("CDATA", array2xml($array));

        $this->assertSame(eval_protected("phpversion()", "_SERVER"), phpversion());
        $this->assertSame(eval_protected("phpversion()", ["_SERVER"]), phpversion());

        $this->assertSame(xml2array('<?xml version="1.0" encoding="asd" ?><a></a>'), ["a" => ""]);
        $this->assertSame(xml2array("<?xml version='1.0' encoding='asd' ?><a></a>"), ["a" => ""]);

        $array = [];
        set_array($array, "a", "a");
        $this->assertSame(count($array), 1);
        set_array($array, "a", "a");
        $this->assertSame(count($array), 2);
        set_array($array, "a", "a");
        $this->assertSame(count($array), 3);
        $array["a#0"] = "a";
        $this->assertSame(count($array), 4);
        set_array($array, "a", "a");
        $this->assertSame(count($array), 5);
        unset_array($array, "a");
        $this->assertSame(count($array), 0);

        $this->assertSame(fix_key("a#1"), "a");

        $cache = get_cache_file("xml/config.xml", ".arr");
        if (file_exists($cache)) {
            unlink($cache);
        }
        $this->assertFileDoesNotExist($cache);
        $this->assertSame(is_array(xmlfile2array("xml/config.xml", false)), true);
        $this->assertFileDoesNotExist($cache);
        $this->assertSame(is_array(xmlfile2array("xml/config.xml", true)), true);
        $this->assertFileExists($cache);
        $this->assertSame(is_array(xmlfile2array("xml/config.xml", true)), true);
        $this->assertFileExists($cache);

        $xml = '<a global="id" require="apps/emails/php/getmail.php" ifeval="false" eval="true">"b"</a>';
        $array = eval_attr(xml2array($xml));
        $this->assertCount(0, $array);

        $xml = '<a global="id" require="apps/emails/php/getmail.php" ifeval="true" eval="true">"b"</a>';
        $array = eval_attr(xml2array($xml));
        $this->assertSame($array, ["a" => "b"]);

        test_external_exec("php/xml01.php", "xmlerror.log", "file not found: nada");
        test_external_exec("php/xml02.php", "xmlerror.log", "could not acquire the semaphore");
        test_external_exec("php/xml03.php", "xmlerror.log", "encoding tag error");
        test_external_exec("php/xml04.php", "xmlerror.log", "error 42: attribute redefined at line");
        test_external_exec("php/xml05.php", "xmlerror.log", "error 42: attribute redefined on file nada at line");
        test_external_exec("php/xml06.php", "xmlerror.log", "unknown tag type with name nada");
        test_external_exec("php/xml07.php", "xmlerror.log", "unknown tag type with name nada on file nada");
        test_external_exec("php/xml08.php", "xmlerror.log", "evaluation error: void expression");
        test_external_exec("php/xml09.php", "xmlerror.log", "require nada not found");
        test_external_exec("php/xml10.php", "xmlerror.log", "unknown boolean value nada");
        test_external_exec("php/xml11.php", "", "");
    }
}
