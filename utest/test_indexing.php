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
 * Test indexing
 *
 * This test performs some tests to validate the correctness
 * of the indexing functions
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
require_once "php/lib/indexing.php";

/**
 * Main class of this unit test
 */
final class test_indexing extends TestCase
{
    #[testdox('indexing functions')]
    /**
     * indexing test
     *
     * This test performs some tests to validate the correctness
     * of the indexing functions
     */
    public function test_indexing(): void
    {
        $this->assertSame(make_index("customers", -1), -3);

        $query = "ALTER TABLE app_customers_index RENAME TO app_customers_index_old";
        db_query($query);
        $this->assertSame(make_index("customers", -1), -2);
        $this->assertCount(2, __make_index_helper("app_customers_index_old"));

        $query = "ALTER TABLE app_customers_index_old RENAME TO app_customers_index";
        db_query($query);
        $this->assertSame(make_index("customers", -1), -3);

        __make_index_helper("app_invoices");
        __make_index_helper("app_invoices");
        __make_index_helper("app_invoices_concepts");
        __make_index_helper("app_invoices_concepts", 1);
        __make_index_helper("app_invoices_concepts", -1);
    }
}
