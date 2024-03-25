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
 * Test customers
 *
 * This test performs some tests to validate the correctness
 * of the customers functions
 */

/**
 * Importing namespaces
 */
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\DependsExternal;

/**
 * Main class of this unit test
 */
final class test_customers extends TestCase
{
    #[testdox('customers functions')]
    /**
     * customers test
     *
     * This test performs some tests to validate the correctness
     * of the customers functions
     */
    public function test_customers(): void
    {
        $array = [
            "nombre" => "Asd Qwerty",
            "nombre1" => "Asd",
            "nombre2" => "Qwerty",
            "nombre_poblacion" => "Barcelona",
            "nombre_codpostal" => "08001",
        ];
        $query = make_insert_query("app_customers", $array);
        db_query($query);

        $id = execute_query("SELECT MAX(id) FROM app_customers");
        $this->assertTrue($id > 0);

        $this->assertSame(make_control("customers", $id), 1);
        $this->assertSame(make_index("customers", $id), 1);
        $this->assertSame(add_version("customers", $id), 1);

        $this->assertCount(1, get_version("customers", $id, 1));

        $array = [
            "nombre1" => "ASD",
            "nombre2" => "QWERTY",
        ];
        $query = make_update_query("app_customers", $array, "id=$id");
        db_query($query);

        $this->assertSame(make_control("customers", $id), -4);
        $this->assertSame(make_index("customers", $id), 2);
        $this->assertSame(add_version("customers", $id), 1);

        $this->assertCount(1, get_version("customers", $id, 2));

        $array = [
            "cif" => "123456789",
        ];
        $query = make_update_query("app_customers", $array, "id=$id");
        db_query($query);

        $this->assertSame(make_control("customers", $id), -4);
        $this->assertSame(make_index("customers", $id), 2);
        $this->assertSame(add_version("customers", $id), 1);

        $this->assertCount(1, get_version("customers", $id, 3));

        $query = "DELETE FROM app_customers WHERE id=$id";
        db_query($query);

        $this->assertSame(make_control("customers", $id), 2);
        $this->assertSame(make_index("customers", $id), 3);
        $this->assertSame(add_version("customers", $id), 2);

        $this->assertCount(0, get_version("customers", $id, 1));

        $this->assertSame(make_control("customers", $id), -3);
        $this->assertSame(make_index("customers", $id), -3);
        $this->assertSame(add_version("customers", $id), -3);
    }
}
