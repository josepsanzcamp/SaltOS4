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
 * Test control
 *
 * This test performs some tests to validate the correctness
 * of the control functions
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
require_once __ROOT__ . 'php/lib/control.php';
require_once __ROOT__ . 'php/lib/log.php';
require_once __ROOT__ . 'php/lib/version.php';
require_once __ROOT__ . 'php/lib/indexing.php';

/**
 * Main class of this unit test
 */
final class test_control extends TestCase
{
    #[testdox('control functions')]
    /**
     * control test
     *
     * This test performs some tests to validate the correctness
     * of the control functions
     */
    public function test_control(): void
    {
        $this->assertSame(make_control('dashboard', -1), -1);
        $this->assertSame(make_log('dashboard', -1, 'utest'), -1);
        $this->assertSame(make_index('dashboard', -1), -1);
        $this->assertSame(make_version('dashboard', -1), -1);
        $this->assertSame(get_version('dashboard', -1), -1);
        $this->assertSame(del_version('dashboard', -1), -1);
        $this->assertSame(make_log('emails', -1, 'utest'), -2);
        $this->assertSame(make_version('emails', -1), -2);
        $this->assertSame(get_version('emails', -1), -2);
        $this->assertSame(del_version('emails', -1), -2);

        $this->assertSame(make_control('customers', -1), -3);

        $query = 'ALTER TABLE app_customers_control RENAME TO app_customers_control_old';
        db_query($query);

        $this->assertSame(make_control('customers', -1), -2);

        $query = 'ALTER TABLE app_customers_control_old RENAME TO app_customers_control';
        db_query($query);

        $this->assertSame(make_control('customers', -1), -3);
    }
}
