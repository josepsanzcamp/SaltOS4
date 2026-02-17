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
 *
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
 */

declare(strict_types=1);

// phpcs:disable PSR1.Classes.ClassDeclaration
// phpcs:disable Squiz.Classes.ValidClassName
// phpcs:disable PSR1.Methods.CamelCapsMethodName
// phpcs:disable PSR1.Files.SideEffects

/**
 * Test IDs
 *
 * This test performs some tests to validate the correctness
 * of the check_ids feature
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
final class test_ids extends TestCase
{
    #[testdox('IDs functions')]
    /**
     * IDs test
     *
     * This test performs some tests to validate the correctness
     * of the check_ids feature
     */
    public function test_ids(): void
    {
        $this->assertSame(check_ids(null), '0');
        $this->assertSame(check_ids(true), '1');
        $this->assertSame(check_ids(false), '0');
        $this->assertSame(check_ids([]), '0');
        $this->assertSame(check_ids(''), '0');
        $this->assertSame(check_ids(), '0');
        $this->assertSame(check_ids('1,2,3', '2,1'), '1,2,3');
        $this->assertSame(check_ids('a,b,c', 'b,a'), '0');

        $this->assertSame(check_ids_array(null), []);
        $this->assertSame(check_ids_array(true), [1]);
        $this->assertSame(check_ids_array(false), []);
        $this->assertSame(check_ids_array([]), []);
        $this->assertSame(check_ids_array(''), []);
        $this->assertSame(check_ids_array(), []);
        $this->assertSame(check_ids_array('1,2,3', '2,1'), [1, 2, 3]);
        $this->assertSame(check_ids_array('a,b,c', 'b,a'), []);
    }
}
