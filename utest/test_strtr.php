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
 * Test mime
 *
 * This test performs some tests to validate the correctness
 * of the str_replace_assoc instead of the strtr function
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
final class test_strtr extends TestCase
{
    #[testdox('strtr functions')]
    /**
     * strtr test
     *
     * This test performs some tests to validate the correctness
     * of the str_replace_assoc instead of the strtr function
     */
    public function test_strtr(): void
    {
        // phpcs:disable Generic.Files.LineLength
        $lorem = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';
        // phpcs:enable Generic.Files.LineLength
        $iterations = 100000;
        $expected = str_replace_assoc([
            'a' => 'b',
            'e' => 'f',
            'i' => 'j',
            'o' => 'p',
            'u' => 'v',
        ], $lorem);

        $time0 = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $output = str_replace([
                'a', 'e', 'i', 'o', 'u',
            ], [
                'b', 'f', 'j', 'p', 'v',
            ], $lorem);
        }
        $this->assertSame($output, $expected);

        $time1 = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $output = strtr($lorem, [
                'a' => 'b',
                'e' => 'f',
                'i' => 'j',
                'o' => 'p',
                'u' => 'v',
            ]);
        }
        $this->assertSame($output, $expected);

        $time2 = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $output = str_replace_assoc([
                'a' => 'b',
                'e' => 'f',
                'i' => 'j',
                'o' => 'p',
                'u' => 'v',
            ], $lorem);
        }
        $this->assertSame($output, $expected);

        $time3 = microtime(true);

        $time3 = $time3 - $time2;
        $time2 = $time2 - $time1;
        $time1 = $time1 - $time0;

        //~ print_r([
            //~ "time1" => sprintf("%f", $time1),
            //~ "time2" => sprintf("%f", $time2),
            //~ "time3" => sprintf("%f", $time3),
        //~ ]);

        $this->assertTrue($time1 < $time2);
        $this->assertTrue($time2 > $time3);
        $this->assertTrue($time1 < $time3);
    }
}
