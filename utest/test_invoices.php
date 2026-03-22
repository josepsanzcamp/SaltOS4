<?php

/**
 *  ____        _ _    ___  ____    _  _    _
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / |
 * \___ \ / _` | | __| | | \___ \  | || |_ | |
 *  ___) | (_| | | |_| |_| |___) | |__   _|| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)_|
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
require_once 'php/lib/version.php';
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
            'customer_name' => 'Asd Qwerty',
            'proforma_code' => '',
        ];
        $query = make_insert_query('app_invoices', $array);
        db_query($query);

        $id = execute_query('SELECT MAX(id) FROM app_invoices');
        $this->assertTrue($id > 0);

        $this->assertSame(-3, del_version('invoices', $id));

        $array = [
            'invoice_id' => $id,
            'description' => 'Clock',
            'quantity' => '1',
            'price' => '99.99',
        ];
        $query = make_insert_query('app_invoices_lines', $array);
        db_query($query);

        $array = [
            'invoice_id' => $id,
            'description' => 'Belt',
            'quantity' => '1',
            'price' => '19.99',
        ];
        $query = make_insert_query('app_invoices_lines', $array);
        db_query($query);

        $this->assertSame(make_control('invoices', $id), 1);
        $this->assertSame(make_version('invoices', $id), 1);
        $this->assertSame(make_index('invoices', $id), 1);

        $this->assertSame(-3, get_version('invoices', $id, 0));
        $this->assertCount(6, get_version('invoices', $id, 1));
        $this->assertSame(-3, get_version('invoices', $id, 2));

        $array = [
            'customer_name' => 'ASD QWERTY',
            'proforma_code' => '',
        ];
        $query = make_update_query('app_invoices', $array, [
            'id' => $id,
        ]);
        db_query($query);

        $id2 = execute_query('SELECT MAX(id) FROM app_invoices_lines');

        $array = [
            'invoice_id' => $id,
            'description' => 'Belt',
            'quantity' => '1',
            'price' => '29.99',
        ];
        $query = make_update_query('app_invoices_lines', $array, [
            'id' => $id2,
        ]);
        db_query($query);

        $array = [
            'invoice_id' => $id,
            'description' => 'Tools',
            'quantity' => '1',
            'price' => '9.99',
        ];
        $query = make_insert_query('app_invoices_lines', $array);
        db_query($query);

        $this->assertSame(make_control('invoices', $id), -4);
        $this->assertSame(make_version('invoices', $id), 1);
        $this->assertSame(make_index('invoices', $id), 2);

        $this->assertSame(-3, get_version('invoices', $id, 0));
        $this->assertCount(6, get_version('invoices', $id, 1));
        $this->assertCount(6, get_version('invoices', $id, 2 + 0));
        $this->assertSame(-3, get_version('invoices', $id, 3));

        $array = [
            'customer_name' => 'Asd Qwerty',
            'proforma_code' => '123456789',
        ];
        $query = make_update_query('app_invoices', $array, [
            'id' => $id,
        ]);
        db_query($query);

        $id3 = execute_query("SELECT MIN(id) FROM app_invoices_lines WHERE invoice_id=$id");

        $query = "DELETE FROM app_invoices_lines WHERE id=$id3";
        db_query($query);

        $this->assertSame(make_control('invoices', $id), -4);
        $this->assertSame(make_version('invoices', $id), 1);
        $this->assertSame(make_index('invoices', $id), 2);

        $this->assertSame(-3, get_version('invoices', $id, 0));
        $this->assertCount(6, get_version('invoices', $id, 1));
        $this->assertCount(6, get_version('invoices', $id, 2 + 0));
        $this->assertCount(6, get_version('invoices', $id, 3 + 0));
        $this->assertSame(-3, get_version('invoices', $id, 4));

        $query = "DELETE FROM app_invoices WHERE id=$id";
        db_query($query);

        $query = "DELETE FROM app_invoices_lines WHERE invoice_id=$id";
        db_query($query);

        $this->assertSame(make_control('invoices', $id), 2);
        $this->assertSame(make_version('invoices', $id), -4);
        $this->assertSame(make_index('invoices', $id), 3);

        $this->assertCount(6, get_version('invoices', $id, 1));

        $this->assertSame(make_control('invoices', $id), -3);
        $this->assertSame(make_version('invoices', $id), -4);
        $this->assertSame(make_index('invoices', $id), -3);

        $this->assertSame(1, del_version('invoices', $id));
    }
}
