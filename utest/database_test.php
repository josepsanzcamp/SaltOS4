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

// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration
// phpcs:disable Squiz.Classes.ValidClassName
// phpcs:disable PSR1.Methods.CamelCapsMethodName

use PHPUnit\Framework\TestCase;

chdir("code/api");

require_once "php/autoload/database.php";
require_once "php/autoload/error.php";
require_once "php/autoload/strings.php";
require_once "php/autoload/log.php";
require_once "php/autoload/datetime.php";
require_once "php/autoload/server.php";
require_once "php/autoload/file.php";
require_once "php/autoload/config.php";
require_once "php/autoload/output.php";
require_once "php/autoload/version.php";
require_once "php/autoload/exec.php";
require_once "php/autoload/sql.php";
require_once "php/autoload/semaphores.php";

final class database_test extends TestCase
{
    private function test_helper($obj, $number_to_string = false): void
    {
        ini_set("date.timezone", "Europe/Madrid");

        // First test part
        $query = "SELECT 1";
        $result = [
            "total" => 1,
            "header" => [1],
            "rows" => [
                [1 => $number_to_string ? "1" : 1],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // GROUP_CONCAT test part
        $query = "SELECT GROUP_CONCAT(a) test FROM (SELECT 1 a UNION SELECT 2 a UNION SELECT 3 a) a;";
        $result = [
            "total" => 1,
            "header" => ["test"],
            "rows" => [
                ["test" => "1,2,3"],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // REPLACE test part
        $query = "SELECT REPLACE('abc', 'b', 'c') test";
        $result = [
            "total" => 1,
            "header" => ["test"],
            "rows" => [
                ["test" => "acc"],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // LPAD test part
        $query = "SELECT LPAD('123', '5', '0') test";
        $result = [
            "total" => 1,
            "header" => ["test"],
            "rows" => [
                ["test" => "00123"],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // CONCAT test part
        $query = "SELECT CONCAT('a', 'b', 'c') test";
        $result = [
            "total" => 1,
            "header" => ["test"],
            "rows" => [
                ["test" => "abc"],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // CONCAT_WS test part
        $query = "SELECT CONCAT_WS(',','a','b','c',null,true,false) test";
        $result = [
            "total" => 1,
            "header" => ["test"],
            "rows" => [
                ["test" => "a,b,c,1,0"],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // UNIX_TIMESTAMP test part
        $query = "SELECT UNIX_TIMESTAMP('2024-02-01 12:34:56') test";
        $result = [
            "total" => 1,
            "header" => ["test"],
            "rows" => [
                ["test" => $number_to_string ? "1706787296" : 1706787296],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // FROM_UNIXTIME test part
        $query = "SELECT FROM_UNIXTIME(1706787296) test";
        $result = [
            "total" => 1,
            "header" => ["test"],
            "rows" => [
                ["test" => "2024-02-01 12:34:56"],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // YEAR test part
        $query = "SELECT YEAR('2024-02-01 12:34:56') test";
        $result = [
            "total" => 1,
            "header" => ["test"],
            "rows" => [
                ["test" => $number_to_string ? "2024" : 2024],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // MONTH test part
        $query = "SELECT MONTH('2024-02-01 12:34:56') test";
        $result = [
            "total" => 1,
            "header" => ["test"],
            "rows" => [
                ["test" => $number_to_string ? "2" : 2],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // WEEK test part
        $query = "SELECT WEEK('2024-02-01 12:34:56', 1) test";
        $result = [
            "total" => 1,
            "header" => ["test"],
            "rows" => [
                ["test" => $number_to_string ? "5" : 5],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // TRUNCATE test part
        $query = "SELECT TRUNCATE(1.2345, 2) test";
        $result = [
            "total" => 1,
            "header" => ["test"],
            "rows" => [
                ["test" => "1.23"],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // DAY test part
        $query = "SELECT DAY('2024-02-01 12:34:56') test";
        $result = [
            "total" => 1,
            "header" => ["test"],
            "rows" => [
                ["test" => $number_to_string ? "1" : 1],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // DAYOFYEAR test part
        $query = "SELECT DAYOFYEAR('2024-02-01 12:34:56') test";
        $result = [
            "total" => 1,
            "header" => ["test"],
            "rows" => [
                ["test" => $number_to_string ? "32" : 32],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // DAYOFWEEK test part
        $query = "SELECT DAYOFWEEK('2024-02-01 12:34:56') test";
        $result = [
            "total" => 1,
            "header" => ["test"],
            "rows" => [
                ["test" => $number_to_string ? "5" : 5],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // HOUR test part
        $query = "SELECT HOUR('2024-02-01 12:34:56') test";
        $result = [
            "total" => 1,
            "header" => ["test"],
            "rows" => [
                ["test" => $number_to_string ? "12" : 12],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // MINUTE test part
        $query = "SELECT MINUTE('2024-02-01 12:34:56') test";
        $result = [
            "total" => 1,
            "header" => ["test"],
            "rows" => [
                ["test" => $number_to_string ? "34" : 34],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // SECOND test part
        $query = "SELECT SECOND('2024-02-01 12:34:56') test";
        $result = [
            "total" => 1,
            "header" => ["test"],
            "rows" => [
                ["test" => $number_to_string ? "56" : 56],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // MD5 test part
        $query = "SELECT MD5('fortuna') test";
        $result = [
            "total" => 1,
            "header" => ["test"],
            "rows" => [
                ["test" => md5("fortuna")],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // REPEAT test part
        $query = "SELECT REPEAT('abc',3) test";
        $result = [
            "total" => 1,
            "header" => ["test"],
            "rows" => [
                ["test" => "abcabcabc"],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // FIND_IN_SET test part
        $query = "SELECT FIND_IN_SET(3,'1,2,3,4,5') test";
        $result = [
            "total" => 1,
            "header" => ["test"],
            "rows" => [
                ["test" => $number_to_string ? "3" : 3],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        $query = "SELECT FIND_IN_SET(6,'1,2,3,4,5') test";
        $result = [
            "total" => 1,
            "header" => ["test"],
            "rows" => [
                ["test" => $number_to_string ? "0" : 0],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        $query = "SELECT FIND_IN_SET(3,'12345') test";
        $result = [
            "total" => 1,
            "header" => ["test"],
            "rows" => [
                ["test" => $number_to_string ? "0" : 0],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // IF test part
        $query = "SELECT IF(true, 'ok', 'ko') test";
        $result = [
            "total" => 1,
            "header" => ["test"],
            "rows" => [
                ["test" => "ok"],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        $query = "SELECT IF(false, 'ok', 'ko') test";
        $result = [
            "total" => 1,
            "header" => ["test"],
            "rows" => [
                ["test" => "ko"],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        $query = "SELECT IF(null, 'ok', 'ko') test";
        $result = [
            "total" => 1,
            "header" => ["test"],
            "rows" => [
                ["test" => "ko"],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // POW test part
        $query = "SELECT POW(2, 8) test";
        $result = [
            "total" => 1,
            "header" => ["test"],
            "rows" => [
                ["test" => $number_to_string ? "256" : 256.],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // DATE_FORMAT test part
        $query = "SELECT DATE_FORMAT('2024-02-01 12:34:56', '%Y-%m-%d %H:%i:%s') test";
        $result = [
            "total" => 1,
            "header" => ["test"],
            "rows" => [
                ["test" => "2024-02-01 12:34:56"],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);

        // NOW test part
        $query = "SELECT NOW() test";
        $result = [
            "total" => 1,
            "header" => ["test"],
            "rows" => [
                ["test" => current_datetime()],
            ],
        ];
        $this->assertSame($obj->db_query($query), $result);
    }

    public function test_pdo_mysql(): void
    {
        // Connection part
        $obj = db_connect([
            "type" => "pdo_mysql",
            "host" => "localhost",
            "port" => "3306",
            "name" => "saltos",
            "user" => "saltos",
            "pass" => "saltos",
        ]);
        $this->assertSame($obj instanceof database_pdo_mysql, true);

        // Helper part
        $this->test_helper($obj);

        // Close connection
        $obj->db_disconnect();
    }

    public function test_mysqli(): void
    {
        // Connection part
        $obj = db_connect([
            "type" => "mysqli",
            "host" => "localhost",
            "port" => "3306",
            "name" => "saltos",
            "user" => "saltos",
            "pass" => "saltos",
        ]);
        $this->assertSame($obj instanceof database_mysqli, true);

        // Helper part
        $this->test_helper($obj, true);

        // Close connection
        $obj->db_disconnect();
    }

    public function test_pdo_sqlite(): void
    {
        // Connection part
        $obj = db_connect([
            "type" => "pdo_sqlite",
            "file" => "data/files/saltos.sqlite",
        ]);
        $this->assertSame($obj instanceof database_pdo_sqlite, true);

        // Helper part
        $this->test_helper($obj);

        // Close connection
        $obj->db_disconnect();
    }

    public function test_sqlite3(): void
    {
        // Connection part
        $obj = db_connect([
            "type" => "sqlite3",
            "file" => "data/files/saltos.sqlite",
        ]);
        $this->assertSame($obj instanceof database_sqlite3, true);

        // Helper part
        $this->test_helper($obj);

        // Close connection
        $obj->db_disconnect();
    }

    /*public function test_pdo_mssql(): void
    {
        // Connection part
        $obj = db_connect([
            "type" => "pdo_mssql",
            "host" => "localhost",
            "port" => "3306",
            "name" => "saltos",
            "user" => "saltos",
            "pass" => "saltos",
        ]);
        $this->assertSame($obj instanceof database_pdo_mssql, true);

        // Helper part
        $this->test_helper($obj);

        // Close connection
        $obj->db_disconnect();
    }*/
}
