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
require_once 'lib/utestlib.php';

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
        $query = make_insert_query('app_customers', [
            'name' => 'The SaltOS project',
            'code' => '12345678X',
            'city' => 'Barcelona',
            'zip' => '08001',
        ]);
        $this->assertSame($query, 'INSERT INTO app_customers(name,city,zip,code) ' .
            "VALUES('The SaltOS project','Barcelona','08001','12345678X')");

        // Default update
        $query = make_update_query('app_customers', [
            'name' => 'The SaltOS project',
            'code' => '12345678X',
            'city' => 'Barcelona',
            'zip' => '08001',
        ], [
            'id' => 1,
        ]);
        $this->assertSame($query, "UPDATE app_customers SET name='The SaltOS project'," .
            "city='Barcelona',zip='08001',code='12345678X' WHERE (id='1')");

        // Testing normal behavior
        $query = make_where_query('app_customers', [
            'name' => 'The SaltOS project',
            'code' => '12345678X',
            'city' => 'Barcelona',
            'zip' => '08001',
        ]);
        $this->assertSame($query, "(name='The SaltOS project' AND " .
            "city='Barcelona' AND zip='08001' AND code='12345678X')");

        // Testing the parse_query feature
        $query = parse_query('/*MYSQL mysql *//*SQLITE sqlite *//* other */');
        $this->assertSame($query, 'mysql');

        $query = parse_query('/*/*MYSQL mysql *//*SQLITE sqlite *//* other *//*');
        $this->assertSame($query, '/*mysql/*');

        set_config('db/type', 'pdo_sqlite');
        $this->assertSame(__parse_query_type(), 'SQLITE');

        set_config('db/type', 'sqlite3');
        $this->assertSame(__parse_query_type(), 'SQLITE');

        set_config('db/type', 'mysqli');
        $this->assertSame(__parse_query_type(), 'MYSQL');

        set_config('db/type', 'pdo_mysql');
        $this->assertSame(__parse_query_type(), 'MYSQL');

        $this->assertSame(__parse_query_strpos("c'babcbabc", 'a'), false);
        $this->assertSame(__parse_query_strpos("c'babc'babc", 'a'), 8);

        // Testing the automatic output of execute_query
        $result = execute_query('SELECT 1 a');
        $this->assertSame($result, 1);

        $result = execute_query('SELECT 1 a UNION SELECT 2 a');
        $this->assertSame($result, [1, 2]);

        $result = execute_query('SELECT 1 a,2 b');
        $this->assertSame($result, ['a' => 1, 'b' => 2]);

        $result = execute_query('SELECT 1 a,2 b UNION SELECT 3 a,4 b');
        $this->assertSame($result, [['a' => 1, 'b' => 2], ['a' => 3, 'b' => 4]]);

        // Testing the automatic output of execute_query_array
        $result = execute_query_array('SELECT 1 a');
        $this->assertSame($result, [1]);

        $result = execute_query_array('SELECT 1 a UNION SELECT 2 a');
        $this->assertSame($result, [1, 2]);

        $result = execute_query_array('SELECT 1 a,2 b');
        $this->assertSame($result, [['a' => 1, 'b' => 2]]);

        $result = execute_query_array('SELECT 1 a,2 b UNION SELECT 3 a,4 b');
        $this->assertSame($result, [['a' => 1, 'b' => 2], ['a' => 3, 'b' => 4]]);

        // Testing helpers for retrieve the fields, tables, types and sizes
        $fields = get_fields('tbl_config');
        $this->assertSame(count($fields), 4);

        $fields = get_indexes('tbl_config');
        $this->assertSame(count($fields), 2);

        db_disconnect();
        set_config('db/type', 'pdo_sqlite');
        db_connect();

        $fields = get_fields('tbl_config');
        $this->assertSame(count($fields), 4);

        $fields = get_indexes('tbl_config');
        $this->assertSame(count($fields), 2);

        db_disconnect();
        set_config('db/type', 'pdo_mysql');
        db_connect();

        $tables = get_tables();
        $this->assertContains('app_customers', $tables);

        $type = get_field_type('TINYTEXT');
        $this->assertSame($type, 'string');

        $size = get_field_size('TINYTEXT');
        $this->assertSame($size, 255);

        // Test for searching features
        $query = make_like_query('', 'hola mundo');
        $this->assertSame($query, '1=1');

        $query = make_like_query('key,val', '');
        $this->assertSame($query, '1=1');

        $query = make_like_query('key,,val', '+hola -mundo');
        $this->assertSame($query, "((key LIKE '%hola%' OR val LIKE '%hola%') AND " .
            "(key NOT LIKE '%mundo%' AND val NOT LIKE '%mundo%'))");

        $query = make_fulltext_query('', 'customers');
        $this->assertSame($query, '1=1');

        $query = make_fulltext_query('', 'dashboard');
        $this->assertSame($query, '1=1');

        $query = make_fulltext_query('', 'configlog');
        $this->assertSame($query, '1=1');

        $query = make_fulltext_query('nada', 'configlog');
        $this->assertNotSame($query, '1=1');

        $query = make_fulltext_query('+hola -mundo', 'customers');
        $this->assertSame($query, 'id IN (SELECT id FROM app_customers_index ' .
            "WHERE MATCH(search) AGAINST('+(+\"hola\" -\"mundo\")' IN BOOLEAN MODE))");

        test_external_exec('php/sql1.php', 'phperror.log', 'unknown type nada');
        test_external_exec('php/sql2.php', 'phperror.log', 'unknown type nada');
        test_external_exec('php/sql3.php', 'phperror.log', 'unknown type nada');
        test_external_exec('php/sql4.php', 'phperror.log', 'unused data nada');
        test_external_exec('php/sql5.php', 'phperror.log', 'unused data nada');
    }
}
