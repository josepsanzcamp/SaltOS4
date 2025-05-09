<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2025 by Josep Sanz Campderrós
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
 * Test database drivers
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
final class test_database extends TestCase
{
    /**
     * Helper
     *
     * This function executes the follow queries and checks the correctness
     * of the driver by comparing the results with the expected results.
     *
     * The tests that performs are the follow:
     * - SELECT GROUP_CONCAT(a) test FROM (SELECT 1 a UNION SELECT 2 a UNION SELECT 3 a) a;
     * - SELECT REPLACE('abc', 'b', 'c') test
     * - SELECT LPAD('123', '5', '0') test
     * - SELECT CONCAT('a', 'b', 'c') test
     * - SELECT CONCAT_WS(',','a','b','c',null,true,false) test
     * - SELECT UNIX_TIMESTAMP('2024-02-01 12:34:56') test
     * - SELECT FROM_UNIXTIME(1706787296) test
     * - SELECT YEAR('2024-02-01 12:34:56') test
     * - SELECT MONTH('2024-02-01 12:34:56') test
     * - SELECT WEEK('2024-02-01 12:34:56', 1) test
     * - SELECT TRUNCATE(1.2345, 2) test
     * - SELECT DAY('2024-02-01 12:34:56') test
     * - SELECT DAYOFYEAR('2024-02-01 12:34:56') test
     * - SELECT DAYOFWEEK('2024-02-01 12:34:56') test
     * - SELECT HOUR('2024-02-01 12:34:56') test
     * - SELECT MINUTE('2024-02-01 12:34:56') test
     * - SELECT SECOND('2024-02-01 12:34:56') test
     * - SELECT MD5('fortuna') test
     * - SELECT REPEAT('abc',3) test
     * - SELECT FIND_IN_SET(3,'1,2,3,4,5') test
     * - SELECT FIND_IN_SET(6,'1,2,3,4,5') test
     * - SELECT FIND_IN_SET(3,'12345') test
     * - SELECT IF(true, 'ok', 'ko') test
     * - SELECT IF(false, 'ok', 'ko') test
     * - SELECT IF(null, 'ok', 'ko') test
     * - SELECT POW(2, 8) test
     * - SELECT DATE_FORMAT('2024-02-01 12:34:56', '%Y-%m-%d %H:%i:%s') test
     * - SELECT NOW() test
     */
    private function test_helper($obj): void
    {
        ini_set('date.timezone', 'Europe/Madrid');

        // First test part
        $query = "SELECT '1' test";
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => 1],
            ],
        ];
        $this->assertEquals($obj->db_query($query), $result);

        // GROUP_CONCAT test part
        $query = 'SELECT GROUP_CONCAT(a) test FROM (SELECT 1 a UNION SELECT 2 a UNION SELECT 3 a) a;';
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => '1,2,3'],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // REPLACE test part
        $query = "SELECT REPLACE('abc', 'b', 'c') test";
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => 'acc'],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // LPAD test part
        $query = "SELECT LPAD('123', '5', '0') test";
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => '00123'],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // CONCAT test part
        $query = "SELECT CONCAT('a', 'b', 'c') test";
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => 'abc'],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // CONCAT_WS test part
        $query = "SELECT CONCAT_WS(',','a','b','c',null,true,false) test";
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => 'a,b,c,1,0'],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // UNIX_TIMESTAMP test part
        $query = "SELECT UNIX_TIMESTAMP('2024-02-01 12:34:56') test";
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => 1706787296],
            ],
        ];
        $this->assertEquals($obj->db_query($query), $result);

        // FROM_UNIXTIME test part
        $query = 'SELECT FROM_UNIXTIME(1706787296) test';
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => '2024-02-01 12:34:56'],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // YEAR test part
        $query = "SELECT YEAR('2024-02-01 12:34:56') test";
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => 2024],
            ],
        ];
        $this->assertEquals($obj->db_query($query), $result);

        // MONTH test part
        $query = "SELECT MONTH('2024-02-01 12:34:56') test";
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => 2],
            ],
        ];
        $this->assertEquals($obj->db_query($query), $result);

        // WEEK test part
        $query = "SELECT WEEK('2024-02-01 12:34:56', 1) test";
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => 5],
            ],
        ];
        $this->assertEquals($obj->db_query($query), $result);

        // TRUNCATE test part
        $query = 'SELECT TRUNCATE(1.2345, 2) test';
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => '1.23'],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // DAY test part
        $query = "SELECT DAY('2024-02-01 12:34:56') test";
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => 1],
            ],
        ];
        $this->assertEquals($obj->db_query($query), $result);

        // DAYOFYEAR test part
        $query = "SELECT DAYOFYEAR('2024-02-01 12:34:56') test";
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => 32],
            ],
        ];
        $this->assertEquals($obj->db_query($query), $result);

        // DAYOFWEEK test part
        $query = "SELECT DAYOFWEEK('2024-02-01 12:34:56') test";
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => 5],
            ],
        ];
        $this->assertEquals($obj->db_query($query), $result);

        // HOUR test part
        $query = "SELECT HOUR('2024-02-01 12:34:56') test";
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => 12],
            ],
        ];
        $this->assertEquals($obj->db_query($query), $result);

        // MINUTE test part
        $query = "SELECT MINUTE('2024-02-01 12:34:56') test";
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => 34],
            ],
        ];
        $this->assertEquals($obj->db_query($query), $result);

        // SECOND test part
        $query = "SELECT SECOND('2024-02-01 12:34:56') test";
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => 56],
            ],
        ];
        $this->assertEquals($obj->db_query($query), $result);

        // MD5 test part
        $query = "SELECT MD5('fortuna') test";
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => md5('fortuna')],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // REPEAT test part
        $query = "SELECT REPEAT('abc',3) test";
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => 'abcabcabc'],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // FIND_IN_SET test part
        $query = "SELECT FIND_IN_SET(3,'1,2,3,4,5') test";
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => 3],
            ],
        ];
        $this->assertEquals($obj->db_query($query), $result);

        $query = "SELECT FIND_IN_SET(6,'1,2,3,4,5') test";
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => 0],
            ],
        ];
        $this->assertEquals($obj->db_query($query), $result);

        $query = "SELECT FIND_IN_SET(3,'12345') test";
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => 0],
            ],
        ];
        $this->assertEquals($obj->db_query($query), $result);

        // IF test part
        $query = "SELECT IF(true, 'ok', 'ko') test";
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => 'ok'],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        $query = "SELECT IF(false, 'ok', 'ko') test";
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => 'ko'],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        $query = "SELECT IF(null, 'ok', 'ko') test";
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => 'ko'],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // POW test part
        $query = 'SELECT POW(2, 8) test';
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => 256],
            ],
        ];
        $this->assertEquals($obj->db_query($query), $result);

        // DATE_FORMAT test part
        $query = "SELECT DATE_FORMAT('2024-02-01 12:34:56', '%Y-%m-%d %H:%i:%s') test";
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => '2024-02-01 12:34:56'],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // NOW test part
        $query = 'SELECT NOW() test';
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => current_datetime()],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // Check part
        $query = 'SELECT 1';
        $this->assertEquals($obj->db_check($query), true);

        $query = 'SELECT 1';
        $this->assertEquals($obj->db_check($query), true);

        $query = 'SELECT a';
        $this->assertEquals($obj->db_check($query), false);

        // This is for improve the coverage of all tests
        $query = 'SELECT id FROM tbl_users_tokens';
        $result = $obj->db_query($query, 'auto');

        $query = 'SELECT id,token FROM tbl_users_tokens';
        $result = $obj->db_query($query, 'auto');

        $query = 'SELECT id FROM tbl_users_tokens';
        $result = $obj->db_query($query, 'concat');

        $query = 'SELECT 1';
        $result = $obj->db_query($query, 'concat');

        $query = 'SELECT 1 WHERE 1=0';
        $result = $obj->db_query($query, 'concat');

        $query = 'SELECT 1 UNION SELECT 2';
        $result = $obj->db_query($query, 'concat');

        $query = 'SELECT 1 UNION SELECT 2';
        $result = $obj->db_query($query, 'column');

        $this->assertSame(is_array($obj->db_query('')), true);

        if (function_exists('__libsqlite_group_concat_step')) {
            $this->assertEquals(__libsqlite_group_concat_step('a', null, 'b', ','), 'a,b');
        }

        if (function_exists('__libsqlite_group_concat_finalize')) {
            $this->assertEquals(__libsqlite_group_concat_finalize('a,b', null), 'a,b');
        }

        if (function_exists('__libsqlite_replace')) {
            $this->assertEquals(__libsqlite_replace('asd', 's', 'x'), 'axd');
        }

        // To test the prepared statements feature
        $query = 'SELECT 1 WHERE ? =';
        $this->assertEquals($obj->db_check($query, [1]), false);

        $query = 'SELECT 1 WHERE ? = ?';
        $this->assertEquals($obj->db_check($query, [1, 0]), true);

        $query = 'SELECT 1 WHERE ? = ?';
        $this->assertEquals($obj->db_check($query, [1, 1]), true);

        $query = 'SELECT 1 WHERE ? = ?';
        $this->assertEquals($obj->db_query($query, [1, 1]), [
            'total' => 1,
            'header' => [
                0 => 1,
            ],
            'rows' => [
                0 => [
                    1 => 1,
                ],
            ],
        ]);

        // To test the types (int and string)
        $query = 'SELECT * FROM tbl_users LIMIT 1';
        $result = [
            'total' => 1,
            'header' => [
                'id',
                'active',
                'group_id',
                'login',
                'name',
                'description',
                'start',
                'end',
                'days',
                'groups_id',
            ],
            'rows' => [
                [
                    'id' => 1,
                    'active' => 1,
                    'group_id' => 1,
                    'login' => 'admin',
                    'name' => 'Admin',
                    'description' => 'Admin user',
                    'start' => '00:00:00',
                    'end' => '23:59:59',
                    'days' => '1111111',
                    'groups_id' => '',
                ],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);
    }

    /**
     * Last Insert Id helper
     *
     * This function is intended to check the correctness of the last_insert_id
     * feature implemented in each database driver.
     */
    private function last_insert_id_helper($obj): void
    {
        $query = prepare_insert_query('tbl_config', [
            'user_id' => 1,
            'key' => 'keyExample',
            'val' => 'valExample',
        ]);
        $obj->db_query(...$query);

        $last_id = $obj->db_last_insert_id();

        $result = $obj->db_query('SELECT MAX(id) FROM tbl_config');
        $this->assertSame($result, [
            'total' => 1,
            'header' => ['MAX(id)'],
            'rows' => [
                ['MAX(id)' => $last_id],
            ],
        ]);

        $obj->db_query('DELETE FROM tbl_config WHERE id = ?', [$last_id]);
    }

    #[testdox('pdo_mysql driver')]
    /**
     * PDO MySQL driver
     *
     * This function checks the correctness of the sqlite3 driver by creating a
     * database connection, sendint queries validating the expected results and
     * closing the connection.
     */
    public function test_pdo_mysql(): void
    {
        // Connection part
        $obj = db_connect([
            'type' => 'pdo_mysql',
            'name' => 'saltos',
            'user' => 'saltos',
            'pass' => 'saltos',
        ]);
        $this->assertSame($obj instanceof database_pdo_mysql, true);

        // Helper part
        $this->test_helper($obj);

        // Specific part
        $this->assertSame($obj->db_escape('\'"%'), '\\\'\"%');

        // Last insert id part
        $this->last_insert_id_helper($obj);

        // Close connection
        $obj->db_disconnect();

        test_external_exec('php/database01_pdo_mysql.php', 'dberror.log', 'connection refused');
        test_external_exec('php/database02_pdo_mysql.php', 'dberror.log', "base table or view not found table saltos.nada doesn't exist");
    }

    #[testdox('mysqli driver')]
    /**
     * MySQL improved driver
     *
     * This function checks the correctness of the sqlite3 driver by creating a
     * database connection, sendint queries validating the expected results and
     * closing the connection.
     */
    public function test_mysqli(): void
    {
        // Connection part
        $obj = db_connect([
            'type' => 'mysqli',
            'name' => 'saltos',
            'user' => 'saltos',
            'pass' => 'saltos',
        ]);
        $this->assertSame($obj instanceof database_mysqli, true);

        // Helper part
        $this->test_helper($obj);

        // Specific part
        $this->assertSame($obj->db_escape('\'"%'), '\\\'\"%');

        // Last insert id part
        $this->last_insert_id_helper($obj);

        // Close connection
        $obj->db_disconnect();

        test_external_exec('php/database01_mysqli.php', 'dberror.log', 'connection refused');
        test_external_exec('php/database02_mysqli.php', 'dberror.log', "table saltos.nada doesn't exist");
    }

    #[testdox('pdo_sqlite driver')]
    /**
     * PDO SQLite driver
     *
     * This function checks the correctness of the sqlite3 driver by creating a
     * database connection, sendint queries validating the expected results and
     * closing the connection.
     */
    public function test_pdo_sqlite(): void
    {
        // Connection part
        $obj = db_connect([
            'type' => 'pdo_sqlite',
            'file' => 'data/files/saltos.sqlite',
        ]);
        $this->assertSame($obj instanceof database_pdo_sqlite, true);

        // Helper part
        $this->test_helper($obj);

        // Specific part
        $this->assertSame($obj->db_escape('\'"%'), '\'\'"%');

        // Last insert id part
        $this->last_insert_id_helper($obj);

        // Close connection
        $obj->db_disconnect();

        test_external_exec('php/database01_pdo_sqlite.php', '', '');
        test_external_exec('php/database02_pdo_sqlite.php', 'dberror.log', 'general error: 1 no such table: nada');
        test_external_exec('php/database03_pdo_sqlite.php', 'dberror.log', 'file /root not writable');
        test_external_exec('php/database04_pdo_sqlite.php', 'dberror.log', 'general error: 26 file is not a database');
        test_external_exec('php/database05_pdo_sqlite.php', 'dberror.log', 'could not acquire the semaphore');
        test_external_exec('php/database06_pdo_sqlite.php', 'dberror.log', 'general error: 1 no such table: nada');
    }

    #[testdox('sqlite3 driver')]
    /**
     * SQLite3 driver
     *
     * This function checks the correctness of the sqlite3 driver by creating a
     * database connection, sendint queries validating the expected results and
     * closing the connection.
     */
    public function test_sqlite3(): void
    {
        // Connection part
        $obj = db_connect([
            'type' => 'sqlite3',
            'file' => 'data/files/saltos.sqlite',
        ]);
        $this->assertSame($obj instanceof database_sqlite3, true);

        // Helper part
        $this->test_helper($obj);

        // Specific part
        $this->assertSame($obj->db_escape('\'"%'), '\'\'"%');

        // Last insert id part
        $this->last_insert_id_helper($obj);

        // Close connection
        $obj->db_disconnect();

        test_external_exec('php/database01_sqlite3.php', '', '');
        test_external_exec('php/database02_sqlite3.php', 'dberror.log', 'unable to prepare statement: no such table: nada');
        test_external_exec('php/database03_sqlite3.php', 'dberror.log', 'file /root not writable');
        test_external_exec('php/database04_sqlite3.php', 'dberror.log', 'unable to prepare statement: file is not a database');
        test_external_exec('php/database05_sqlite3.php', 'dberror.log', 'could not acquire the semaphore');
        test_external_exec('php/database06_sqlite3.php', 'dberror.log', 'unable to prepare statement: no such table: nada');
    }

    // @phpstan-ignore method.unused
    #[testdox('pdo_mssql driver')]
    /**
     * PDO mssql driver
     *
     * This function checks the correctness of the sqlserver driver by creating a
     * database connection, sendint queries validating the expected results and
     * closing the connection.
     */
    private function test_pdo_mssql(): void
    {
        $mssql = intval(ob_passthru('ps uaxw | grep sqlservr | grep -v grep | wc -l'));
        $this->assertTrue($mssql > 0, 'SQL Server not found');

        // Connection part
        $obj = db_connect([
            'type' => 'pdo_mssql',
            'name' => 'master',
            'user' => 'sa',
            'pass' => 'asd123ASD',
        ]);
        $this->assertSame($obj instanceof database_pdo_mssql, true);

        // First test part
        $query = "SELECT '1' test";
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => 1],
            ],
        ];
        $this->assertEquals($obj->db_query($query), $result);

        // REPLACE test part
        $query = "SELECT REPLACE('abc', 'b', 'c') test";
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => 'acc'],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // CONCAT test part
        $query = "SELECT CONCAT('a', 'b', 'c') test";
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => 'abc'],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // YEAR test part
        $query = "SELECT YEAR('2024-02-01 12:34:56') test";
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => 2024],
            ],
        ];
        $this->assertEquals($obj->db_query($query), $result);

        // MONTH test part
        $query = "SELECT MONTH('2024-02-01 12:34:56') test";
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => 2],
            ],
        ];
        $this->assertEquals($obj->db_query($query), $result);

        // DAY test part
        $query = "SELECT DAY('2024-02-01 12:34:56') test";
        $result = [
            'total' => 1,
            'header' => ['test'],
            'rows' => [
                ['test' => 1],
            ],
        ];
        $this->assertEquals($obj->db_query($query), $result);

        // Check part
        $query = 'SELECT 1';
        $this->assertEquals($obj->db_check($query), true);

        $query = 'SELECT a';
        $this->assertEquals($obj->db_check($query), false);

        // This is for improve the coverage of all tests
        $query = 'SELECT name FROM spt_values';
        $result = $obj->db_query($query, 'auto');

        $query = 'SELECT name,number FROM spt_values';
        $result = $obj->db_query($query, 'auto');

        $query = 'SELECT name FROM spt_values';
        $result = $obj->db_query($query, 'concat');

        $query = 'SELECT name FROM spt_values WHERE 1=0';
        $result = $obj->db_query($query, 'concat');

        $this->assertSame(is_array($obj->db_query('')), true);

        // Specific part
        $this->assertSame($obj->db_escape('\'"%'), '\'\'"%');

        // To test the prepared statements feature
        $query = 'SELECT 1 WHERE ? = ?';
        $this->assertEquals($obj->db_check($query, [1, 1]), true);

        $query = 'SELECT 1 WHERE ? = ?';
        $this->assertEquals($obj->db_query($query, [1, 1]), [
            'total' => 1,
            'header' => [
                0 => 'computed',
            ],
            'rows' => [
                0 => [
                    'computed' => 1,
                ],
            ],
        ]);

        // Close connection
        $obj->db_disconnect();

        test_external_exec('php/database01_pdo_mssql.php', 'dberror.log', 'unable to connect: Adaptive Server is unavailable or does not exist');
        test_external_exec('php/database02_pdo_mssql.php', 'dberror.log', 'general error: 20018 invalid object name nada');
    }

    #[testdox('database driver')]
    /**
     * Database driver
     *
     * This function checks the correctness of the database driver by creating a
     * database connection, sendint queries validating the expected results and
     * closing the connection.
     */
    public function test_database(): void
    {
        $this->assertSame(get_config('db/obj') instanceof database_pdo_mysql, true);

        $this->assertSame(db_check('SELECT * FROM tbl_users_tokens'), true);

        $result = db_query('SELECT * FROM tbl_users_tokens');
        $this->assertSame(is_array($result), true);

        $result = db_query('SELECT * FROM tbl_users_tokens');
        $this->assertSame(count(db_fetch_row($result)) > 0, true);

        $result = db_query('SELECT * FROM tbl_users_tokens');
        $this->assertSame(count(db_fetch_all($result)) > 0, true);

        $result = db_query('SELECT * FROM tbl_users_tokens');
        $this->assertSame(db_num_rows($result) > 0, true);

        $result = db_query('SELECT * FROM tbl_users_tokens');
        $this->assertSame(db_num_fields($result) > 0, true);

        $result = db_query('SELECT * FROM tbl_users_tokens');
        $this->assertSame(db_field_name($result, 0), 'id');

        if (file_exists('data/logs/dbwarning.log')) {
            unlink('data/logs/dbwarning.log');
        }
        $this->assertFileDoesNotExist('data/logs/dbwarning.log');
        set_config('debug/slowquerytime', 0);
        $result = db_query('SELECT * FROM tbl_users_tokens WHERE id = ?', [0]);
        set_config('debug/slowquerytime', 5);
        db_free($result);
        $this->assertFileExists('data/logs/dbwarning.log');
        if (file_exists('data/logs/dbwarning.log')) {
            unlink('data/logs/dbwarning.log');
        }

        // Last Insert Id part
        $query = prepare_insert_query('tbl_config', [
            'user_id' => 1,
            'key' => 'keyExample',
            'val' => 'valExample',
        ]);
        db_query(...$query);

        $last_id = db_last_insert_id();

        $result = db_query('SELECT MAX(id) FROM tbl_config');
        $this->assertSame($result, [
            'total' => 1,
            'header' => ['MAX(id)'],
            'rows' => [
                ['MAX(id)' => $last_id],
            ],
        ]);

        db_query('DELETE FROM tbl_config WHERE id = ?', [$last_id]);

        // Continue
        db_disconnect();
        $this->assertSame(db_check('SELECT * FROM tbl_users_tokens'), false);
        db_connect();
        db_connect(); // This second call cover the case when args is null and db/obj has link

        $this->assertSame(db_escape('\'"%'), '\\\'\"%');

        test_external_exec('php/database07.php', 'dberror.log', 'database type nada not found');
        test_external_exec('php/database08.php', 'dberror.log', 'unknown database connector');
        test_external_exec('php/database09.php', 'dberror.log', 'unknown database connector');
        test_external_exec('php/database10.php', 'dberror.log', 'unknown field name at position nada');
        test_external_exec('php/database11.php', 'dberror.log', 'unknown database connector');
        test_external_exec('php/database12.php', 'dberror.log', 'unknown database connector');
        test_external_exec('php/database13.php', 'dberror.log', 'unknown database connector');
    }
}
