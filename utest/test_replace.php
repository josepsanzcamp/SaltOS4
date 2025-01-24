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
 * Test mime
 *
 * This test performs some tests to validate the correctness
 * of the str_replace instead of the preg_replace
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
final class test_replace extends TestCase
{
    #[testdox('replace functions')]
    /**
     * replace test
     *
     * This test performs some tests to validate the correctness
     * of the str_replace_assoc instead of the replace function
     */
    public function test_replace(): void
    {
        $lorem = [];
        for ($i = 0; $i < 2000; $i++) {
            $lorem[] = "Lorem$i ipsum dolor sit amet.";
        }
        $lorem = implode(' ', $lorem);
        $iterations = 10000;
        $from = 'Lorem777';
        $to = 'Ipsum777';
        $expected = preg_replace('/' . preg_quote($from, '/') . '/', $to, $lorem, 1);

        $time0 = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $output = preg_replace('/' . preg_quote($from, '/') . '/', $to, $lorem, 1);
        }
        $this->assertSame($output, $expected);

        $time1 = microtime(true);

        // Notes: this part uses the str_replace that replaces all occurrences
        for ($i = 0; $i < $iterations; $i++) {
            $output = str_replace($from, $to, $lorem);
        }
        $this->assertSame($output, $expected);

        $time2 = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $output = str_replace_one($from, $to, $lorem);
        }
        $this->assertSame($output, $expected);

        $time3 = microtime(true);

        $time3 = $time3 - $time2;
        $time2 = $time2 - $time1;
        $time1 = $time1 - $time0;

        print_r([
            'time1' => sprintf('%f', $time1),
            'time2' => sprintf('%f', $time2),
            'time3' => sprintf('%f', $time3),
        ]);

        $this->assertTrue($time2 < $time1);
        $this->assertTrue($time3 < $time2);
    }
}
