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
 * Test array
 *
 * This test performs some tests to validate the correctness
 * of the array functions
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
final class test_array extends TestCase
{
    #[testdox('array functions')]
    /**
     * array test
     *
     * This test performs some tests to validate the correctness
     * of the array functions
     */
    public function test_array(): void
    {
        $this->assertSame(array_protected(null), []);
        $this->assertSame(array_protected(""), []);
        $this->assertSame(array_protected("hola"), ["hola"]);
        $this->assertSame(array_protected(true), [true]);
        $this->assertSame(array_protected(false), [false]);
        $this->assertSame(array_protected(1.23), [1.23]);
        $this->assertSame(array_protected([1, 2, 3]), [1, 2, 3]);

        $xml = '<a b="c" d="e"></a>';
        $array = xml2array($xml);
        $array["a"] = join4array($array["a"]);
        $this->assertSame($array, ["a" => ["b" => "c", "d" => "e"]]);

        $xml = '<a b="c" d="e">f</a>';
        $array = xml2array($xml);
        $array["a"] = join4array($array["a"]);
        $this->assertSame($array, ["a" => ["b" => "c", "d" => "e", "value" => "f"]]);

        $xml = '<a b="c" d="e"><f>g</f><h>i</h></a>';
        $array = xml2array($xml);
        $array["a"] = join4array($array["a"]);
        $this->assertSame($array, ["a" => ["b" => "c", "d" => "e", "f" => "g", "h" => "i"]]);
    }
}
