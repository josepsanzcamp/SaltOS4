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
 * Test invoices
 *
 * This test performs some tests to validate the correctness
 * of the invoices functions
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
require_once 'php/lib/control.php';
require_once 'php/lib/indexing.php';

/**
 * Main class of this unit test
 */
final class test_invoices extends TestCase
{
    #[testdox('invoices functions')]
    /**
     * invoices test
     *
     * This test performs some tests to validate the correctness
     * of the invoices functions
     */
    public function test_invoices(): void
    {
        $array = [
            'nombre' => 'Asd Qwerty',
            'num' => '',
        ];
        $query = make_insert_query('app_invoices', $array);
        db_query($query);

        $id = execute_query('SELECT MAX(id) FROM app_invoices');
        $this->assertTrue($id > 0);

        $array = [
            'id_factura' => $id,
            'concepto' => 'Clock',
            'unidades' => '1',
            'precio' => '99.99',
        ];
        $query = make_insert_query('app_invoices_concepts', $array);
        db_query($query);

        $array = [
            'id_factura' => $id,
            'concepto' => 'Belt',
            'unidades' => '1',
            'precio' => '19.99',
        ];
        $query = make_insert_query('app_invoices_concepts', $array);
        db_query($query);

        $this->assertSame(make_control('invoices', $id), 1);
        $this->assertSame(make_index('invoices', $id), 1);
        $this->assertSame(add_version('invoices', $id), 1);

        $this->assertCount(0, get_version('invoices', $id, 0));
        $this->assertCount(5, get_version('invoices', $id, 1));
        $this->assertCount(0, get_version('invoices', $id, 2));

        $array = [
            'nombre' => 'ASD QWERTY',
            'num' => '',
        ];
        $query = make_update_query('app_invoices', $array, "id=$id");
        db_query($query);

        $id2 = execute_query('SELECT MAX(id) FROM app_invoices_concepts');

        $array = [
            'id_factura' => $id,
            'concepto' => 'Belt',
            'unidades' => '1',
            'precio' => '29.99',
        ];
        $query = make_update_query('app_invoices_concepts', $array, "id=$id2");
        db_query($query);

        $array = [
            'id_factura' => $id,
            'concepto' => 'Tools',
            'unidades' => '1',
            'precio' => '9.99',
        ];
        $query = make_insert_query('app_invoices_concepts', $array);
        db_query($query);

        $this->assertSame(make_control('invoices', $id), -4);
        $this->assertSame(make_index('invoices', $id), 2);
        $this->assertSame(add_version('invoices', $id), 1);

        $this->assertCount(0, get_version('invoices', $id, 0));
        $this->assertCount(5, get_version('invoices', $id, 1));
        $this->assertCount(5, get_version('invoices', $id, 2));
        $this->assertCount(0, get_version('invoices', $id, 3));

        $array = [
            'nombre' => 'Asd Qwerty',
            'num' => '123456789',
        ];
        $query = make_update_query('app_invoices', $array, "id=$id");
        db_query($query);

        $id3 = execute_query("SELECT MIN(id) FROM app_invoices_concepts WHERE id_factura=$id");

        $query = "DELETE FROM app_invoices_concepts WHERE id=$id3";
        db_query($query);

        $this->assertSame(make_control('invoices', $id), -4);
        $this->assertSame(make_index('invoices', $id), 2);
        $this->assertSame(add_version('invoices', $id), 1);

        $this->assertCount(0, get_version('invoices', $id, 0));
        $this->assertCount(5, get_version('invoices', $id, 1));
        $this->assertCount(5, get_version('invoices', $id, 2));
        $this->assertCount(5, get_version('invoices', $id, 3));
        $this->assertCount(0, get_version('invoices', $id, 4));

        $query = "DELETE FROM app_invoices WHERE id=$id";
        db_query($query);

        $query = "DELETE FROM app_invoices_concepts WHERE id_factura=$id";
        db_query($query);

        $this->assertSame(make_control('invoices', $id), 2);
        $this->assertSame(make_index('invoices', $id), 3);
        $this->assertSame(add_version('invoices', $id), 2);

        $this->assertCount(0, get_version('invoices', $id, 1));

        $this->assertSame(make_control('invoices', $id), -3);
        $this->assertSame(make_index('invoices', $id), -3);
        $this->assertSame(add_version('invoices', $id), -3);
    }
}
