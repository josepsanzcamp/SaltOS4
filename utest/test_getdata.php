<?php

/**
 *  ____        _ _    ___  ____  _  _
 * / ___|  __ _| | |_ / _ \/ ___|| || |
 * \___ \ / _` | | __| | | \___ \| || |_
 *  ___) | (_| | | |_| |_| |___) |__   _|
 * |____/ \__,_|_|\__|\___/|____/   |_|
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
 * Test getdata
 *
 * This test performs some tests to validate the correctness
 * of the getdata functions
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
final class test_getdata extends TestCase
{
    #[testdox('getdata functions')]
    /**
     * getdata test
     *
     * This test performs some tests to validate the correctness
     * of the getdata functions
     */
    public function test_getdata(): void
    {
        set_data('server/token', get_unique_token());
        $this->assertSame(is_array(get_data('server')), true);
        $this->assertSame(count(get_data('server')), 1);
        $this->assertSame(strlen(get_data('server/token')), 36);

        set_data('server/token', null);
        $this->assertSame(is_array(get_data('server')), true);
        $this->assertSame(count(get_data('server')), 0);
        $this->assertSame(get_data('server/token'), null);

        set_data('server/token', get_unique_token());
        set_data('server', null);
        $this->assertSame(get_data('server'), null);
        $this->assertSame(get_data('server/token'), null);

        set_data('token', get_unique_token());
        $this->assertSame(strlen(get_data('token')), 36);
        set_data('token', null);
        $this->assertSame(get_data('token'), null);

        test_external_exec('php/getdata1.php', 'phperror.log', 'key nada/nada/nada not found');
        test_external_exec('php/getdata2.php', 'phperror.log', 'key nada/nada/nada not found');

        set_data('rest/0', 'app');
        set_data('rest/1', 'emails');
        set_data('rest/2', 'view');
        set_data('rest/3', '123');
        $this->assertSame(get_data('rest/-1'), '123');

        set_data('json/details/id', 'hola mundo');
        $this->assertSame(get_data('json/details/id'), 'hola mundo');

        set_data('json/details/id', null);
    }
}
