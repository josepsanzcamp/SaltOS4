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
 * Test color
 *
 * This test performs some tests to validate the correctness
 * of the color feature
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
require_once 'php/lib/color.php';

/**
 * Main class of this unit test
 */
final class test_color extends TestCase
{
    #[testdox('color functions')]
    /**
     * color test
     *
     * This test performs some tests to validate the correctness
     * of the color feature
     */
    public function test_color(): void
    {
        $r = color2dec('#336699', 'R');
        $this->assertSame($r, 51);

        $g = color2dec('#336699', 'G');
        $this->assertSame($g, 102);

        $b = color2dec('#336699', 'B');
        $this->assertSame($b, 153);

        $r = color2dec('#369', 'R');
        $this->assertSame($r, 51);

        $g = color2dec('#369', 'G');
        $this->assertSame($g, 102);

        $b = color2dec('#369', 'B');
        $this->assertSame($b, 153);

        test_external_exec('php/color1.php', 'phperror.log', 'unknown color length');
        test_external_exec('php/color2.php', 'phperror.log', 'unknown color component');
    }
}
