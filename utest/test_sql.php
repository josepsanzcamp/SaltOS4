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

        $xml = '<table name="app_customers_index">
            <fields>
                <field name="id" type="/*MYSQL INT(11) *//*SQLITE INTEGER */" pkey="true"/>
                <field name="search" type="MEDIUMTEXT"/>
            </fields>
            <indexes>
                <index fulltext="true" fields="search"/>
            </indexes>
        </table>';
        $tablespec = xml2array($xml);
        $query = parse_query(sql_create_table($tablespec["table"]));
        $this->assertSame($query, "CREATE TABLE app_customers_index (" .
            "id INT(11) PRIMARY KEY AUTO_INCREMENT," .
            "search MEDIUMTEXT NOT NULL DEFAULT ''" .
            ") ENGINE=Mroonga CHARSET=utf8mb4");

        $xml = '<index name="tbl_utest_search" table="tbl_utest" fulltext="true" fields="search"/>';
        $indexspec = xml2array($xml);
        $query = parse_query(sql_create_index($indexspec["index"]));
        $this->assertSame($query, "CREATE FULLTEXT INDEX tbl_utest_search ON tbl_utest (search)");

        // Test for the sql functions used by dbschema
        $query = "DROP TABLE tbl_utest";
        db_query($query);

        $xml = '<table name="tbl_utest">
            <fields>
                <field name="id" type="/*MYSQL INT(11) *//*SQLITE INTEGER */" pkey="true"/>
                <field name="user_id" type="INT(11)" fkey="tbl_users"/>
                <field name="key" type="VARCHAR(255)"/>
                <field name="val" type="VARCHAR(255)"/>
                <field name="val1" type="INT(11)"/>
                <field name="val2" type="DECIMAL(9,2)"/>
                <field name="val3" type="DATE"/>
                <field name="val4" type="TIME"/>
                <field name="val5" type="DATETIME"/>
            </fields>
        </table>';
        $tablespec = xml2array($xml);
        $query = parse_query(sql_create_table($tablespec["table"]));
        $this->assertSame($query, "CREATE TABLE tbl_utest (" .
            "id INT(11) PRIMARY KEY AUTO_INCREMENT," .
            "user_id INT(11) NOT NULL DEFAULT '0'," .
            "`key` VARCHAR(255) NOT NULL DEFAULT ''," .
            "val VARCHAR(255) NOT NULL DEFAULT ''," .
            "val1 INT(11) NOT NULL DEFAULT '0'," .
            "val2 DECIMAL(9,2) NOT NULL DEFAULT '0'," .
            "val3 DATE NOT NULL DEFAULT '0000-00-00'," .
            "val4 TIME NOT NULL DEFAULT '00:00:00'," .
            "val5 DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'," .
            "FOREIGN KEY (user_id) REFERENCES tbl_users (id)" .
            ") ENGINE=Aria CHARSET=utf8mb4");
        db_query($query);

        $query = make_insert_query("tbl_utest", [
            "id" => 1,
            "val" => "nada",
            "val1" => "1",
            "val2" => "3.141592",
            "val3" => "0000-00-00",
            "val4" => "00:00:00",
            "val5" => "0000-00-00 00:00:00",
        ]);
        $this->assertSame($query, "INSERT INTO tbl_utest(id,val,val1,val2,val3,val4,val5) " .
            "VALUES('1','nada','1','3.141592','0000-00-00','00:00:00','0000-00-00 00:00:00')");
        db_query($query);

        $query = make_update_query("tbl_utest", [
            "val" => "nada",
            "val1" => "1",
            "val2" => "3.141592",
            "val3" => "9999-99-99",
            "val4" => "99:99:99",
            "val5" => "9999-99-99 99:99:99",
        ], make_where_query([
            "id" => 1,
        ]));
        $this->assertSame($query, "UPDATE tbl_utest SET val='nada',val1='1',val2='3.141592'," .
            "val3='9999-12-31',val4='24:00:00',val5='9999-12-31 23:59:59' WHERE (id='1')");
        db_query($query);

        $query = sql_insert_from_select("tbl_utest", "tbl_utest");
        $this->assertSame($query, "INSERT INTO tbl_utest(id,user_id,`key`,val,val1,val2,val3,val4,val5) " .
            "SELECT id,user_id,`key`,val,val1,val2,val3,val4,val5 FROM tbl_utest");

        $query = "DROP TABLE tbl_utest";
        db_query($query);

        $query = sql_alter_table("a", "b");
        $this->assertSame($query, "ALTER TABLE a RENAME TO b");

        $query = sql_insert_from_select("tbl_config", "tbl_config");
        $this->assertSame($query, "INSERT INTO tbl_config(id,user_id,`key`,val) " .
            "SELECT id,user_id,`key`,val FROM tbl_config");

        $query = sql_drop_table("a");
        $this->assertSame($query, "DROP TABLE a");

        $xml = '<indexes name="tbl_config" table="tbl_config" fields="user_id,key"/>';
        $indexspec = xml2array($xml);
        $query = sql_create_index($indexspec["indexes"]);
        $this->assertSame($query, "CREATE  INDEX tbl_config ON tbl_config (user_id,`key`)");

        $query = parse_query(sql_drop_index("tbl_config", "tbl_config"));
        $this->assertSame($query, "DROP INDEX tbl_config ON tbl_config");

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
