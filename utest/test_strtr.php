<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
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
        $lorem = [];
        for ($i = 0; $i < 100; $i++) {
            $lorem[] = "Lorem$i ipsum dolor sit amet.";
        }
        $lorem = implode(' ', $lorem);
        $iterations = 10000;
        $expected = str_replace_assoc([
            'a' => 'b',
            'e' => 'f',
            'i' => 'j',
            'o' => 'p',
            'u' => 'v',
        ], $lorem);

        $time0 = microtime(true);

        // This part executes the strtr, that is the most expensive
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

        $time1 = microtime(true);

        // This part executes the str_replace_assoc, the function implemented by me
        // using the str_replace that get the keys and values to call the str_replace
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

        $time2 = microtime(true);

        $time2 = $time2 - $time1;
        $time1 = $time1 - $time0;

        //~ print_r([
            //~ 'time1' => sprintf('%f', $time1),
            //~ 'time2' => sprintf('%f', $time2),
        //~ ]);

        $this->assertTrue($time2 < $time1);
    }
}
