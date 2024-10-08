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
// phpcs:disable Generic.Files.LineLength

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
            'nombre' => 'Asd Qwerty',
            'nombre1' => 'Asd',
            'nombre2' => 'Qwerty',
            'nombre_poblacion' => 'Barcelona',
            'nombre_codpostal' => '08001',
        ];
        $query = make_insert_query('app_customers', $array);
        db_query($query);

        $id = execute_query('SELECT MAX(id) FROM app_customers');
        $this->assertTrue($id > 0);

        $this->assertSame(make_control('customers', $id), 1);
        $this->assertSame(make_index('customers', $id), 1);
        $this->assertSame(add_version('customers', $id), 1);

        $this->assertNull(get_version('customers', $id, 0));
        $this->assertCount(3, get_version('customers', $id, 1));
        $this->assertNull(get_version('customers', $id, 2));

        $array = [
            'nombre1' => 'ASD',
            'nombre2' => 'QWERTY',
        ];
        $query = make_update_query('app_customers', $array, "id=$id");
        db_query($query);

        $this->assertSame(make_control('customers', $id), -4);
        $this->assertSame(make_index('customers', $id), 2);
        $this->assertSame(add_version('customers', $id), 1);

        $this->assertNull(get_version('customers', $id, 0));
        $this->assertCount(3, get_version('customers', $id, 1));
        $this->assertCount(3, get_version('customers', $id, 2));
        $this->assertNull(get_version('customers', $id, 3));

        $array = [
            'cif' => '123456789',
        ];
        $query = make_update_query('app_customers', $array, "id=$id");
        db_query($query);

        $this->assertSame(make_control('customers', $id), -4);
        $this->assertSame(make_index('customers', $id), 2);
        $this->assertSame(add_version('customers', $id), 1);

        $this->assertNull(get_version('customers', $id, 0));
        $this->assertCount(3, get_version('customers', $id, 1));
        $this->assertCount(3, get_version('customers', $id, 2));
        $this->assertCount(3, get_version('customers', $id, 3));
        $this->assertNull(get_version('customers', $id, 4));

        // Check for hash blockchain integrity
        $oldhash = execute_query("SELECT hash
                                  FROM app_customers_version
                                  WHERE reg_id=$id AND ver_id=1");
        $hash = execute_query("SELECT hash
                               FROM app_customers_version
                               WHERE reg_id=$id AND ver_id=2");
        $query = "UPDATE app_customers_version
                  SET hash='nada'
                  WHERE reg_id=$id AND ver_id=2";
        db_query($query);

        file_put_contents('/tmp/phpunit.regid', $id);
        test_external_exec('php/customers1.php', 'phperror.log', 'blockchain integrity break for customers');

        $query = "UPDATE app_customers_version
                  SET hash='$hash'
                  WHERE reg_id=$id AND ver_id=2";
        db_query($query);
        $this->assertCount(3, get_version('customers', $id, 3));

        // Check for datetime blockchain integrity
        $datetime = execute_query("SELECT datetime
                                   FROM app_customers_version
                                   WHERE reg_id=$id AND ver_id=2");
        $query = "UPDATE app_customers_version
                  SET hash='$oldhash', datetime=0
                  WHERE reg_id=$id AND ver_id=2";
        db_query($query);

        $array = execute_query("SELECT user_id, datetime, reg_id, ver_id, data, hash
                                FROM app_customers_version
                                WHERE reg_id=$id AND ver_id=2");
        $newhash = md5(serialize($array));
        $query = "UPDATE app_customers_version
                  SET hash='$newhash'
                  WHERE reg_id=$id AND ver_id=2";
        db_query($query);

        test_external_exec('php/customers1.php', 'phperror.log', 'blockchain integrity break for customers');

        $query = "UPDATE app_customers_version
                  SET hash='$hash', datetime='$datetime'
                  WHERE reg_id=$id AND ver_id=2";
        db_query($query);
        $this->assertCount(3, get_version('customers', $id, 3));

        // Check for ver_id blockchain integrity
        $query = "UPDATE app_customers_version
                  SET hash='$oldhash', ver_id=-2
                  WHERE reg_id=$id AND ver_id=2";
        db_query($query);

        $array = execute_query("SELECT user_id, datetime, reg_id, ver_id, data, hash
                                FROM app_customers_version
                                WHERE reg_id=$id AND ver_id=-2");
        $newhash = md5(serialize($array));
        $query = "UPDATE app_customers_version
                  SET hash='$newhash'
                  WHERE reg_id=$id AND ver_id=-2";
        db_query($query);

        test_external_exec('php/customers1.php', 'phperror.log', 'blockchain integrity break for customers');
        unlink('/tmp/phpunit.regid');

        $query = "UPDATE app_customers_version
                  SET hash='$hash', ver_id=2
                  WHERE reg_id=$id AND ver_id=-2";
        db_query($query);
        $this->assertCount(3, get_version('customers', $id, 3));

        // Continue
        $query = "DELETE FROM app_customers WHERE id=$id";
        db_query($query);

        $this->assertSame(make_control('customers', $id), 2);
        $this->assertSame(make_index('customers', $id), 3);
        $this->assertSame(add_version('customers', $id), 2);

        $this->assertNull(get_version('customers', $id, 1));

        $this->assertSame(make_control('customers', $id), -3);
        $this->assertSame(make_index('customers', $id), -3);
        $this->assertSame(add_version('customers', $id), -3);
    }
}
