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
 * Test sql
 *
 * This test performs some tests to validate the correctness
 * of the sql functions
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
require_once "lib/utestlib.php";

/**
 * Main class of this unit test
 */
final class test_sql extends TestCase
{
    #[testdox('sql functions')]
    /**
     * SQL test
     *
     * This function performs some tests to validate the correctness
     * of the sql functions
     */
    public function test_sql(): void
    {
        // Default insert
        $query = make_insert_query("app_customers", [
            "nombre" => "The SaltOS project",
            "cif" => "12345678X",
            "nombre_poblacion" => "Barcelona",
            "nombre_codpostal" => "08001",
        ]);
        $this->assertSame($query, "INSERT INTO app_customers(nombre,cif,nombre_poblacion,nombre_codpostal) " .
            "VALUES('The SaltOS project','12345678X','Barcelona','08001')");

        // Default update
        $query = make_update_query("app_customers", [
            "nombre" => "The SaltOS project",
            "cif" => "12345678X",
            "nombre_poblacion" => "Barcelona",
            "nombre_codpostal" => "08001",
        ], "id=1");
        $this->assertSame($query, "UPDATE app_customers SET nombre='The SaltOS project',cif='12345678X'," .
            "nombre_poblacion='Barcelona',nombre_codpostal='08001' WHERE id=1");

        // Testing normal behavior
        $query = make_where_query([
            "nombre" => "The SaltOS project",
            "cif" => "12345678X",
            "nombre_poblacion" => "Barcelona",
            "nombre_codpostal" => "08001",
        ]);
        $this->assertSame($query, "(nombre='The SaltOS project' AND cif='12345678X' AND " .
            "nombre_poblacion='Barcelona' AND nombre_codpostal='08001')");

        // Testing extra behavior using simbols like >=, >, < and <=
        $query = make_where_query([
            "nombre>=" => "The SaltOS project",
            "cif>" => "12345678X",
            "nombre_poblacion<" => "Barcelona",
            "nombre_codpostal<=" => "08001",
        ]);
        $this->assertSame($query, "(nombre>='The SaltOS project' AND cif>'12345678X' AND " .
            "nombre_poblacion<'Barcelona' AND nombre_codpostal<='08001')");

        // Testing the parse_query feature
        $query = parse_query("/*MYSQL mysql *//*SQLITE sqlite *//* other */");
        $this->assertSame($query, "mysql");

        $query = parse_query("/*/*MYSQL mysql *//*SQLITE sqlite *//* other *//*");
        $this->assertSame($query, "/*mysql/*");

        set_config("db/type", "pdo_sqlite");
        $this->assertSame(__parse_query_type(), "SQLITE");

        set_config("db/type", "sqlite3");
        $this->assertSame(__parse_query_type(), "SQLITE");

        set_config("db/type", "mysqli");
        $this->assertSame(__parse_query_type(), "MYSQL");

        set_config("db/type", "pdo_mysql");
        $this->assertSame(__parse_query_type(), "MYSQL");

        $this->assertSame(__parse_query_strpos("c'babcbabc", "a"), false);
        $this->assertSame(__parse_query_strpos("c'babc'babc", "a"), 8);

        // Testing the automatic output of execute_query
        $result = execute_query("SELECT 1 a");
        $this->assertSame($result, 1);

        $result = execute_query("SELECT 1 a UNION SELECT 2 a");
        $this->assertSame($result, [1, 2]);

        $result = execute_query("SELECT 1 a,2 b");
        $this->assertSame($result, ["a" => 1, "b" => 2]);

        $result = execute_query("SELECT 1 a,2 b UNION SELECT 3 a,4 b");
        $this->assertSame($result, [["a" => 1, "b" => 2], ["a" => 3, "b" => 4]]);

        // Testing the automatic output of execute_query_array
        $result = execute_query_array("SELECT 1 a");
        $this->assertSame($result, [1]);

        $result = execute_query_array("SELECT 1 a UNION SELECT 2 a");
        $this->assertSame($result, [1, 2]);

        $result = execute_query_array("SELECT 1 a,2 b");
        $this->assertSame($result, [["a" => 1, "b" => 2]]);

        $result = execute_query_array("SELECT 1 a,2 b UNION SELECT 3 a,4 b");
        $this->assertSame($result, [["a" => 1, "b" => 2], ["a" => 3, "b" => 4]]);

        // Testing helpers for retrieve the fields, tables, types and sizes
        $fields = get_fields("tbl_config");
        $this->assertSame(count($fields), 4);

        $fields = get_indexes("tbl_config");
        $this->assertSame(count($fields), 1);

        db_disconnect();
        set_config("db/type", "pdo_sqlite");
        db_connect();

        $fields = get_fields("tbl_config");
        $this->assertSame(count($fields), 4);

        $fields = get_indexes("tbl_config");
        $this->assertSame(count($fields), 2);

        db_disconnect();
        set_config("db/type", "pdo_mysql");
        db_connect();

        $tables = get_tables();
        $this->assertContains("app_customers", $tables);

        $type = get_field_type("TINYTEXT");
        $this->assertSame($type, "string");

        $size = get_field_size("TINYTEXT");
        $this->assertSame($size, 255);

        // Test for searching features
        $query = make_like_query("", "hola mundo");
        $this->assertSame($query, "1=0");

        $query = make_like_query("key,val", "");
        $this->assertSame($query, "1=0");

        $query = make_like_query("key,,val", "+hola -mundo");
        $this->assertSame($query, "((key LIKE '%hola%' OR val LIKE '%hola%') AND " .
            "(key NOT LIKE '%mundo%' AND val NOT LIKE '%mundo%'))");

        $query = make_fulltext_query("", "customers");
        $this->assertSame($query, "1=0");

        $query = make_fulltext_query("", "dashboard");
        $this->assertSame($query, "1=0");

        $query = make_fulltext_query("+hola -mundo", "customers");
        $this->assertSame($query, "id IN (SELECT id FROM app_customers_index " .
            "WHERE MATCH(search) AGAINST('+(+\"hola\" -\"mundo\")' IN BOOLEAN MODE))");

        test_external_exec("php/sql1.php", "phperror.log", "unknown type nada");
        test_external_exec("php/sql2.php", "phperror.log", "unknown type nada");
        test_external_exec("php/sql3.php", "phperror.log", "unknown type nada");
        test_external_exec("php/sql4.php", "phperror.log", "unused data nada");
        test_external_exec("php/sql5.php", "phperror.log", "unused data nada");
    }
}
