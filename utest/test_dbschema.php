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
 * Test dbschema
 *
 * This test performs some tests to validate the correctness
 * of the dbschema functions
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
final class test_dbschema extends TestCase
{
    #[testdox('dbschema functions')]
    /**
     * dbschema test
     *
     * This test performs some tests to validate the correctness
     * of the dbschema functions
     */
    public function test_dbschema(): void
    {
        set_config("xml/dbschema.xml", "nada", 0);
        set_config("xml/dbstatic.xml", "nada", 0);
        db_schema();
        db_schema();
        db_static();
        db_static();

        $tables = get_tables_from_dbschema();
        $this->assertIsArray($tables);
        $this->assertTrue(count($tables) > 0);

        $fields = get_fields_from_dbschema("tbl_users");
        $this->assertIsArray($fields);
        $this->assertTrue(count($fields) > 0);

        $indexes = get_indexes_from_dbschema("tbl_users");
        $this->assertIsArray($indexes);
        $this->assertTrue(count($indexes) > 0);

        $ignores = get_ignores_from_dbschema();
        $this->assertIsArray($ignores);
        $this->assertTrue(count($ignores) > 0);

        $fulltext = get_fulltext_from_dbschema();
        $this->assertIsArray($fulltext);
        $this->assertTrue(count($fulltext) > 0);

        $fkeys = get_fkeys_from_dbschema("tbl_users");
        $this->assertIsArray($fkeys);
        $this->assertTrue(count($fkeys) > 0);
    }
}
