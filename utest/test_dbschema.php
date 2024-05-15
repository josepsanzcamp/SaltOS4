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
 * Loading helper function
 *
 * This file contains the needed function used by the unit tests
 */
require_once "lib/utestlib.php";
require_once "php/lib/dbschema.php";

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
        $query = "CREATE TABLE tbl_one (" .
            "id INT(11) PRIMARY KEY AUTO_INCREMENT" .
            ") ENGINE=Aria CHARSET=utf8mb4";
        db_query($query);

        $query = "CREATE TABLE tbl_utest (" .
            "id INT(11) PRIMARY KEY AUTO_INCREMENT" .
            ") ENGINE=Aria CHARSET=utf8mb4";
        db_query($query);

        $query = "ALTER TABLE tbl_config
            ADD nada INT(11)";
        db_query($query);

        $query = "DROP INDEX token ON tbl_users_tokens";
        db_query($query);

        $query = "CREATE INDEX token ON tbl_users_tokens(expires_at)";
        db_query($query);

        $query = "ALTER TABLE tbl_users
            RENAME TO __tbl_users__";
        db_query($query);

        $query = "ALTER TABLE tbl_perms
            ADD nada INT(11)";
        db_query($query);

        $query = "ALTER TABLE tbl_perms
            RENAME TO __tbl_perms__";
        db_query($query);

        $query = "DROP TABLE tbl_apps";
        db_query($query);

        $query = "CREATE INDEX nada ON tbl_groups(active)";
        db_query($query);

        set_config("xml/dbschema.xml", "nada", 0);
        set_config("xml/dbstatic.xml", "nada", 0);
        db_schema();
        db_schema();
        db_static();
        db_static();

        $query = "DROP TABLE tbl_one";
        db_query($query);

        $query = "DROP TABLE __tbl_utest__";
        db_query($query);

        $tables = get_tables_from_dbschema();
        $this->assertIsArray($tables);
        $this->assertTrue(count($tables) > 0);

        $fields = get_fields_from_dbschema("tbl_users");
        $this->assertIsArray($fields);
        $this->assertTrue(count($fields) > 0);

        $fields = get_fields_from_dbschema("nada");
        $this->assertIsArray($fields);
        $this->assertTrue(count($fields) == 0);

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

        $fkeys = get_fkeys_from_dbschema("nada");
        $this->assertIsArray($fkeys);
        $this->assertTrue(count($fkeys) == 0);

        $apps = get_apps_from_dbstatic();
        $this->assertIsArray($apps);
        $this->assertTrue(count($apps) > 0);

        $field = get_field_from_dbstatic("customers");
        $this->assertSame($field, "nombre");

        test_external_exec("php/dbschema1.php", "phperror.log", "Unknown fn nada");
        test_external_exec("php/dbschema2.php", "phperror.log", "Unknown fn nada");

        $file = "data/logs/phperror.log";
        $this->assertFileDoesNotExist($file);

        $json = test_web_helper("dbschema", [], "");
        $this->assertArrayHasKey("error", $json);
        $this->assertFileExists($file);
        $this->assertTrue(words_exists("permission denied", file_get_contents($file)));
        unlink($file);

        $json = test_cli_helper("dbschema", [], "");
        $this->assertArrayHasKey("db_schema", $json);
        $this->assertArrayHasKey("db_static", $json);
    }

    #[testdox('sql functions')]
    /**
     * sql test
     *
     * This test performs some tests to validate the correctness
     * of the sql functions
     */
    public function test_sql(): void
    {
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
        $query = parse_query(__dbschema_create_table($tablespec["table"]));
        $this->assertSame($query, "CREATE TABLE app_customers_index (" .
            "id INT(11) PRIMARY KEY AUTO_INCREMENT," .
            "search MEDIUMTEXT NOT NULL DEFAULT ''" .
            ") ENGINE=Mroonga CHARSET=utf8mb4");

        $xml = '<index name="tbl_utest_search" table="tbl_utest" fulltext="true" fields="search"/>';
        $indexspec = xml2array($xml);
        $query = parse_query(__dbschema_create_index($indexspec["index"]));
        $this->assertSame($query, "CREATE FULLTEXT INDEX tbl_utest_search ON tbl_utest (search)");

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
        $query = parse_query(__dbschema_create_table($tablespec["table"]));
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

        $query = __dbschema_insert_from_select("tbl_utest", "tbl_utest");
        $this->assertSame($query, "INSERT INTO tbl_utest(id,user_id,`key`,val,val1,val2,val3,val4,val5) " .
            "SELECT id,user_id,`key`,val,val1,val2,val3,val4,val5 FROM tbl_utest");

        $query = "DROP TABLE tbl_utest";
        db_query($query);

        $query = __dbschema_alter_table("a", "b");
        $this->assertSame($query, "ALTER TABLE a RENAME TO b");

        $query = __dbschema_insert_from_select("tbl_config", "tbl_config");
        $this->assertSame($query, "INSERT INTO tbl_config(id,user_id,`key`,val) " .
            "SELECT id,user_id,`key`,val FROM tbl_config");

        $query = __dbschema_drop_table("a");
        $this->assertSame($query, "DROP TABLE a");

        $xml = '<indexes name="tbl_config" table="tbl_config" fields="user_id,key"/>';
        $indexspec = xml2array($xml);
        $query = __dbschema_create_index($indexspec["indexes"]);
        $this->assertSame($query, "CREATE  INDEX tbl_config ON tbl_config (user_id,`key`)");

        $query = parse_query(__dbschema_drop_index("tbl_config", "tbl_config"));
        $this->assertSame($query, "DROP INDEX tbl_config ON tbl_config");
    }
}
