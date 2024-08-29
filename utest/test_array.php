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

/**
 * Loading helper function
 *
 * This file contains the needed function used by the unit tests
 */
require_once 'lib/utestlib.php';

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
        $this->assertSame(array_protected(''), []);
        $this->assertSame(array_protected('hola'), ['hola']);
        $this->assertSame(array_protected(true), [true]);
        $this->assertSame(array_protected(false), [false]);
        $this->assertSame(array_protected(1.23), [1.23]);
        $this->assertSame(array_protected([1, 2, 3]), [1, 2, 3]);

        $xml = '<a b="c" d="e"></a>';
        $array = xml2array($xml);
        $array['a'] = join_attr_value($array['a']);
        $this->assertSame($array, ['a' => ['b' => 'c', 'd' => 'e']]);

        $xml = '<a b="c" d="e">f</a>';
        $array = xml2array($xml);
        $array['a'] = join_attr_value($array['a']);
        $this->assertSame($array, ['a' => ['value' => 'f', 'b' => 'c', 'd' => 'e']]);

        $xml = '<a b="c" d="e"><f>g</f><h>i</h></a>';
        $array = xml2array($xml);
        $array['a'] = join_attr_value($array['a']);
        $this->assertSame($array, ['a' => ['f' => 'g', 'h' => 'i', 'b' => 'c', 'd' => 'e']]);

        $xml = '<a b="c" d="e"><f>g</f><h>i</h></a>';
        $array = xml2array($xml);
        $this->assertSame(__array_getnode('a/f', $array), 'g');
        $this->assertSame(__array_getattr('b', __array_getnode('a', $array)), 'c');
        $this->assertSame(__array_getattr('d', __array_getnode('a', $array)), 'e');
        $this->assertSame(__array_getattr('b', __array_getnode('a', null)), null);
        $this->assertSame(__array_getvalue(__array_getnode('a', $array)), ['f' => 'g', 'h' => 'i']);
        __array_addnode('a/z', $array, 'xyz');
        $this->assertSame(__array_getnode('a/z', $array), 'xyz');
        __array_setnode('a/z', $array, 'zyx');
        $this->assertSame(__array_getnode('a/z', $array), 'zyx');
        __array_delnode('a/z', $array);
        $this->assertSame(__array_getnode('a/z', $array), null);

        $null = null;
        __array_addnode('a/z', $null, 'xyz');
        __array_setnode('a/z', $null, 'xyz');
        __array_delnode('a/z', $null);

        $xml = '<a><b><c></c><d></d></b></a>';
        $array = xml2array($xml);
        __array_addnode('a/b/z', $array, 'xyz');
        $this->assertSame(__array_getnode('a/b/z', $array), 'xyz');
        __array_setnode('a/b/z', $array, 'zyx');
        $this->assertSame(__array_getnode('a/b/z', $array), 'zyx');
        __array_delnode('a/b/z', $array);
        $this->assertSame(__array_getnode('a/b/z', $array), null);

        $array = [
            ['a', 'b', 'c'],
            ['d', 'e', 'f'],
            ['f', 'e', 'd'],
        ];
        $this->assertSame(__array_filter($array, 'c'), [['a', 'b', 'c']]);
        $this->assertSame(__array_filter($array, 'z'), []);
        $this->assertSame(__array_filter($array, 'a d'), []);
        $this->assertSame(__array_filter($array, 'd'), [['d', 'e', 'f'], ['f', 'e', 'd']]);

        $array = [
            [
                'row' => [
                    'name' => 'a1',
                    'surname' => 'b1',
                ],
                'rows' => [
                    ['name' => 'c1', 'surname' => 'd1'],
                    ['name' => 'e1', 'surname' => 'f1'],
                ],
            ],
            [
                'row' => [
                    'name' => 'a2',
                    'surname' => 'b2',
                ],
                'rows' => [
                    ['name' => 'c2', 'surname' => 'd2'],
                    ['name' => 'e2', 'surname' => 'f2'],
                ],
            ],
            [
                'row' => [
                    'name' => 'a3',
                    'surname' => 'b3',
                ],
                'rows' => [
                    ['name' => 'c3', 'surname' => 'd3'],
                    ['name' => 'e3', 'surname' => 'f3'],
                ],
            ],
        ];
        $this->assertSame(__array_filter($array, 'a'), $array);
        $this->assertSame(__array_filter($array, 'c'), $array);
        $this->assertSame(__array_filter($array, '$A=="a2"', true), []);
        __array_apply_patch($array, '/row/0/col/0', 'x');
        $this->assertSame(__array_getnode('0/row/name', $array), 'x');
        __array_apply_patch($array, '/row/1/row/1/col/1', 'y');
        $this->assertSame(__array_getnode('1/rows/1/surname', $array), 'y');
        __array_apply_patch($array, '/row/2/row/0/col/0', 'z');
        $this->assertSame(__array_getnode('2/rows/0/name', $array), 'z');

        test_external_exec('php/array1.php', 'phperror.log', 'path row for nada not found');
        test_external_exec('php/array2.php', 'phperror.log', 'unknown nada for nada');
        test_external_exec('php/array3.php', 'phperror.log', 'invalid xml tag name');
        test_external_exec('php/array4.php', 'phperror.log', 'invalid xml attr name');
    }
}
