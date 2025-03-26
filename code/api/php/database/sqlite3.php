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

/**
 * SQLite3 driver
 *
 * This file implements the MySQL improved driver. This is the recommended driver when you want
 * to use SQLite3 files as database server, this driver solves the concurrence problem using
 * POSIX semaphores, generally it is a good option for setups that don't require a fulltext
 * search optimizations suck as mroonga, intended for a personal usage or demos.
 */

/**
 * Database SQLite3 class
 *
 * This class allow to SaltOS to connect to SQLite databases using the SQLite3 driver
 */
class database_sqlite3
{
    /**
     * Private link variable
     *
     * This private variable contains the link to the database
     */
    private $link = null;

    /**
     * Constructor
     *
     * This public function is intended to stablish the connection to the database
     *
     * @args => is an array with key val pairs
     * @file => the file that contains the database
     *
     * Notes:
     *
     * This database allow to define external functions that can be used from the SQL language,
     * this is a great feature that allow to use SQLite as MySQL, and using this feature of the
     * database, this driver uses the libsqlite to add a lot of features found in MySQL and
     * used in a lot of queries by SaltOS
     */
    public function __construct($args)
    {
        require_once 'php/database/libsqlite.php';
        if (!extension_loaded('sqlite3')) {
            // @codeCoverageIgnoreStart
            show_php_error([
                'dberror' => 'Class SQLite3 not found',
                'details' => 'Try to install php-sqlite package',
            ]);
            // @codeCoverageIgnoreEnd
        }
        $args['file'] = $args['file'] ?? '';
        if (!file_exists($args['file'])) {
            touch($args['file']);
            chmod_protected($args['file'], 0666);
        }
        if (!is_writable($args['file'])) {
            show_php_error(['dberror' => "File '" . $args['file'] . "' not writable"]);
        }
        try {
            $this->link = new SQLite3($args['file']);
        // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            show_php_error(['dberror' => $e->getMessage()]);
            // @codeCoverageIgnoreEnd
        }
        $this->link->enableExceptions(true);
        $this->link->busyTimeout(0);
        $this->db_query('PRAGMA cache_size=2000');
        $this->db_query('PRAGMA synchronous=OFF');
        $this->db_query('PRAGMA foreign_keys=OFF');
        if (!$this->db_check('SELECT GROUP_CONCAT(1)')) {
            // @codeCoverageIgnoreStart
            $this->link->createAggregate(
                'GROUP_CONCAT',
                '__libsqlite_group_concat_step',
                '__libsqlite_group_concat_finalize'
            );
            // @codeCoverageIgnoreEnd
        }
        if (!$this->db_check('SELECT REPLACE(1,2,3)')) {
            // @codeCoverageIgnoreStart
            $this->link->createFunction('REPLACE', '__libsqlite_replace');
            // @codeCoverageIgnoreEnd
        }
        $this->link->createFunction('LPAD', '__libsqlite_lpad');
        $this->link->createFunction('CONCAT', '__libsqlite_concat');
        $this->link->createFunction('CONCAT_WS', '__libsqlite_concat_ws');
        $this->link->createFunction('UNIX_TIMESTAMP', '__libsqlite_unix_timestamp');
        $this->link->createFunction('FROM_UNIXTIME', '__libsqlite_from_unixtime');
        $this->link->createFunction('YEAR', '__libsqlite_year');
        $this->link->createFunction('MONTH', '__libsqlite_month');
        $this->link->createFunction('WEEK', '__libsqlite_week');
        $this->link->createFunction('TRUNCATE', '__libsqlite_truncate');
        $this->link->createFunction('DAY', '__libsqlite_day');
        $this->link->createFunction('DAYOFYEAR', '__libsqlite_dayofyear');
        $this->link->createFunction('DAYOFWEEK', '__libsqlite_dayofweek');
        $this->link->createFunction('HOUR', '__libsqlite_hour');
        $this->link->createFunction('MINUTE', '__libsqlite_minute');
        $this->link->createFunction('SECOND', '__libsqlite_second');
        $this->link->createFunction('MD5', '__libsqlite_md5');
        $this->link->createFunction('REPEAT', '__libsqlite_repeat');
        $this->link->createFunction('FIND_IN_SET', '__libsqlite_find_in_set');
        $this->link->createFunction('IF', '__libsqlite_if');
        $this->link->createFunction('POW', '__libsqlite_pow');
        $this->link->createFunction('DATE_FORMAT', '__libsqlite_date_format');
        $this->link->createFunction('NOW', '__libsqlite_now');
    }

    /**
     * DB Check
     *
     * This public function is intended to check that the query execution will not trigger an error
     *
     * @query => the query that you want to validate
     */
    public function db_check($query, $params = [])
    {
        try {
            $stmt = $this->link->prepare($query);
            foreach ($params as $key => $val) {
                if (is_int($key)) {
                    $key++;
                }
                $stmt->bindValue($key, $val);
            }
            $stmt = $stmt->execute();
            $stmt->finalize();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * DB Escape
     *
     * This public function is intended to escape the special chars to sanitize the string to be used
     * in a sql query
     *
     * @str => the string that you want to sanitize
     */
    public function db_escape($str)
    {
        return $this->link->escapeString($str);
    }

    /**
     * DB Query
     *
     * This public function is intended to execute the query and returns the resultset
     *
     * @query => the query that you want to execute
     * @fetch => the type of fetch that you want to use, can be auto, query, column or concat
     *
     * Notes:
     *
     * The fetch argument can perform an speed up in the execution of the retrieve action, and
     * can modify how the result is returned
     *
     * auto: this fetch method try to detect if the resultset contains one or more columns, and
     * sets the fetch to column (if the resultset only contains one column) or to query (otherwise)
     *
     * query: this fetch method returns all resultset as an array of rows, and each row contain the
     * pair of key val with the name of the field and the value of the field
     *
     * column: this fetch method returns an array where each element is each value of the field of
     * the each row, this is useful when for example do you want to get all ids of a query, with
     * this method you can obtain an array with each value of the array is an id of the resultset
     *
     * concat: this fetch method is an special mode intended to speed up the retrieve of large
     * arrays, this is useful when you want to get all ids of a query and you want to get a big
     * sized array, in this case, is more efficient to get an string separated by commas with all
     * ids instead of an array where each element is an id
     */
    public function db_query($query, ...$args)
    {
        $fetch = 'query';
        $params = [];
        foreach ($args as $arg) {
            if (is_string($arg)) {
                $fetch = $arg;
            }
            if (is_array($arg)) {
                $params = $arg;
            }
        }
        // CONTINUE
        $query = parse_query($query, 'SQLITE');
        $result = ['total' => 0, 'header' => [], 'rows' => []];
        if (!strlen(trim($query))) {
            return $result;
        }
        // Semaphore part
        $timeout = get_config('db/semaphoretimeout') ?? 10000000;
        if (!semaphore_acquire(__FUNCTION__, $timeout)) {
            show_php_error([
                'dberror' => 'Could not acquire the semaphore',
                'query' => $query,
                'params' => $params,
            ]);
        }
        // Do the query
        $stmt = null;
        for (;;) {
            try {
                $stmt = $this->link->prepare($query);
                foreach ($params as $key => $val) {
                    if (is_int($key)) {
                        $key++;
                    }
                    $stmt->bindValue($key, $val);
                }
                $stmt = $stmt->execute();
                break;
            } catch (Exception $e) {
                if ($timeout <= 0) {
                    show_php_error([
                        'dberror' => $e->getMessage(),
                        'query' => $query,
                        'params' => $params,
                    ]);
                } elseif (stripos($e->getMessage(), 'database is locked') !== false) {
                    // @codeCoverageIgnoreStart
                    $timeout -= __semaphore_usleep(rand(0, 1000));
                    // @codeCoverageIgnoreEnd
                } elseif (stripos($e->getMessage(), 'database schema has changed') !== false) {
                    // @codeCoverageIgnoreStart
                    $timeout -= __semaphore_usleep(rand(0, 1000));
                    // @codeCoverageIgnoreEnd
                } else {
                    show_php_error([
                        'dberror' => $e->getMessage(),
                        'query' => $query,
                        'params' => $params,
                    ]);
                }
            }
        }
        semaphore_release(__FUNCTION__);
        // Dump result to matrix
        if (!is_bool($stmt) && $stmt->numColumns() > 0) {
            if ($fetch == 'auto') {
                $fetch = $stmt->numColumns() > 1 ? 'query' : 'column';
            }
            if ($fetch == 'query') {
                while ($row = $stmt->fetchArray(SQLITE3_ASSOC)) {
                    $result['rows'][] = $row;
                }
                $result['total'] = count($result['rows']);
                if ($result['total'] > 0) {
                    $result['header'] = array_keys($result['rows'][0]);
                }
                $stmt->finalize();
            }
            if ($fetch == 'column') {
                while ($row = $stmt->fetchArray(SQLITE3_NUM)) {
                    $result['rows'][] = $row[0];
                }
                $result['total'] = count($result['rows']);
                $result['header'] = ['column'];
                $stmt->finalize();
            }
            if ($fetch == 'concat') {
                if ($row = $stmt->fetchArray(SQLITE3_NUM)) {
                    $result['rows'][] = $row[0];
                }
                while ($row = $stmt->fetchArray(SQLITE3_NUM)) {
                    $result['rows'][0] .= ',' . $row[0];
                }
                $result['total'] = count($result['rows']);
                $result['header'] = ['concat'];
                $stmt->finalize();
            }
        }
        return $result;
    }

    /**
     * DB Last Insert ID
     *
     * This function returns the last insert id
     */
    public function db_last_insert_id()
    {
        return intval($this->link->lastInsertRowID());
    }

    /**
     * DB Disconnect
     *
     * This function close the database connection and sets the link to null
     */
    public function db_disconnect()
    {
        $this->link->close();
        $this->link = null;
    }
}
