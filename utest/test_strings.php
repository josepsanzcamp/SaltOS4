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
 * Test strings
 *
 * This test performs some tests to validate the correctness
 * of the strings functions
 */

/**
 * Importing namespaces
 */
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Depends;

/**
 * Main class of this unit test
 */
final class test_strings extends TestCase
{
    #[testdox('strings functions')]
    /**
     * strings test
     *
     * This test performs some tests to validate the correctness
     * of the strings functions
     */
    public function test_strings(): void
    {
        $this->assertSame(remove_bad_chars('hola' . chr(8) . 'mundo', '-'), 'hola-mundo');
        $this->assertSame(encode_bad_chars(",áeïoù\nñc|!.", '-', '|!'), 'aeiou-nc|!');
        $this->assertSame(sprintr([1, 2]), "Array\n    [0] => 1\n    [1] => 2\n");
        $this->assertSame(strlen(get_unique_id_md5()), 32);
        $this->assertSame(intelligence_cut('hola, mundo', 0), 'hola...');
        $this->assertSame(intelligence_cut('hola, mundo', 1), 'hola...');
        $this->assertSame(intelligence_cut('hola, mundo', 3), 'hola...');
        $this->assertSame(intelligence_cut('hola, mundo', 4), 'hola...');
        $this->assertSame(intelligence_cut('hola, mundo', 5), 'hola...');
        $this->assertSame(intelligence_cut('hola, mundo', 6), 'hola...');
        $this->assertSame(intelligence_cut('hola, mundo', 7), 'hola...');
        $this->assertSame(intelligence_cut('hola, mundo', 8), 'hola...');
        $this->assertSame(intelligence_cut('hola, mundo', 9), 'hola...');
        $this->assertSame(intelligence_cut('hola, mundo', 10), 'hola...');
        $this->assertSame(intelligence_cut('hola, mundo', 11), 'hola, mundo');
        $this->assertSame(normalize_value('1k'), pow(2, 10));
        $this->assertSame(normalize_value('1m'), pow(2, 20));
        $this->assertSame(normalize_value('1g'), pow(2, 30));
        $this->assertSame(normalize_value('x'), 'x');
        mb_detect_order('UTF-7,ISO-8859-1');
        $this->assertSame(getutf8(iconv('UTF-8', 'ISO-8859-1', 'áeïoù')), 'áeïoù');
        $this->assertSame(words_exists('hola', 'hola mundo'), true);
        $this->assertSame(words_exists('adios', 'hola mundo'), false);
        $this->assertSame(str_replace_assoc([
            'hola' => 'adios',
            'mundo' => 'josep',
        ], 'hola mundo'), 'adios josep');
        $this->assertSame(get_part_from_string('0,1,2,3,4', ',', 0), '0');
        $this->assertSame(get_part_from_string('0,1,2,3,4', ',', 2), '2');
        $this->assertSame(get_part_from_string('0,1,2,3,4', ',', 4), '4');
        $this->assertSame(get_part_from_string('0,1,2,3,4', ',', 5), '');
        $this->assertSame(str_replace_one('', '', 'nada'), 'nada');
        $this->assertSame(str_replace_one('', 'nada', 'nada'), 'nada');
        $this->assertSame(str_replace_one('nada', '', 'nada'), '');
        $this->assertSame(str_replace_one('nada', 'nada', 'nada'), 'nada');
    }
}
