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
 * PDO SQLite driver
 *
 * This file implements the MySQL improved driver. This is the recommended driver when you want
 * to use SQLite3 files as database server and it uses the PDO extension to do it, this driver
 * solves the concurrence problem using POSIX semaphores, generally it is a good option for setups
 * that don't require a fulltext search optimizations suck as mroonga, intended for a personal
 * usage or demos.
 */

/**
 * Database PDO SQLite class
 *
 * This class allow to SaltOS to connect to SQLite databases using the PDO driver
 */
class database_pdo_sqlite
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
        if (!extension_loaded('pdo')) {
            // @codeCoverageIgnoreStart
            show_php_error([
                'dberror' => 'Class PDO not found',
                'details' => 'Try to install php-pdo package',
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
            $this->link = new PDO('sqlite:' . $args['file']);
        // @codeCoverageIgnoreStart
        } catch (PDOException $e) {
            show_php_error(['dberror' => $e->getMessage()]);
            // @codeCoverageIgnoreEnd
        }
        $this->link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->link->setAttribute(PDO::ATTR_TIMEOUT, 0);
        $this->db_query('PRAGMA cache_size=2000');
        $this->db_query('PRAGMA synchronous=OFF');
        $this->db_query('PRAGMA foreign_keys=OFF');
        if (!$this->db_check('SELECT GROUP_CONCAT(1)')) {
            // @codeCoverageIgnoreStart
            $this->link->sqliteCreateAggregate(
                'GROUP_CONCAT',
                '__libsqlite_group_concat_step',
                '__libsqlite_group_concat_finalize'
            );
            // @codeCoverageIgnoreEnd
        }
        if (!$this->db_check('SELECT REPLACE(1,2,3)')) {
            // @codeCoverageIgnoreStart
            $this->link->sqliteCreateFunction('REPLACE', '__libsqlite_replace');
            // @codeCoverageIgnoreEnd
        }
        $this->link->sqliteCreateFunction('LPAD', '__libsqlite_lpad');
        $this->link->sqliteCreateFunction('CONCAT', '__libsqlite_concat');
        $this->link->sqliteCreateFunction('CONCAT_WS', '__libsqlite_concat_ws');
        $this->link->sqliteCreateFunction('UNIX_TIMESTAMP', '__libsqlite_unix_timestamp');
        $this->link->sqliteCreateFunction('FROM_UNIXTIME', '__libsqlite_from_unixtime');
        $this->link->sqliteCreateFunction('YEAR', '__libsqlite_year');
        $this->link->sqliteCreateFunction('MONTH', '__libsqlite_month');
        $this->link->sqliteCreateFunction('WEEK', '__libsqlite_week');
        $this->link->sqliteCreateFunction('TRUNCATE', '__libsqlite_truncate');
        $this->link->sqliteCreateFunction('DAY', '__libsqlite_day');
        $this->link->sqliteCreateFunction('DAYOFYEAR', '__libsqlite_dayofyear');
        $this->link->sqliteCreateFunction('DAYOFWEEK', '__libsqlite_dayofweek');
        $this->link->sqliteCreateFunction('HOUR', '__libsqlite_hour');
        $this->link->sqliteCreateFunction('MINUTE', '__libsqlite_minute');
        $this->link->sqliteCreateFunction('SECOND', '__libsqlite_second');
        $this->link->sqliteCreateFunction('MD5', '__libsqlite_md5');
        $this->link->sqliteCreateFunction('REPEAT', '__libsqlite_repeat');
        $this->link->sqliteCreateFunction('FIND_IN_SET', '__libsqlite_find_in_set');
        $this->link->sqliteCreateFunction('IF', '__libsqlite_if');
        $this->link->sqliteCreateFunction('POW', '__libsqlite_pow');
        $this->link->sqliteCreateFunction('DATE_FORMAT', '__libsqlite_date_format');
        $this->link->sqliteCreateFunction('NOW', '__libsqlite_now');
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
            $stmt->execute($params);
            $stmt = null; // like free_result
            return true;
        } catch (PDOException $e) {
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
     *
     * Notes:
     *
     * This driver adds a simple quotes at the beginning and at the finish of ths string, by this
     * reason this function returns the substr(1, -1) of the quoted string.
     */
    public function db_escape($str)
    {
        return substr($this->link->quote($str), 1, -1);
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
                $stmt->execute($params);
                break;
            } catch (PDOException $e) {
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
        if (!is_bool($stmt) && $stmt->columnCount() > 0) {
            if ($fetch == 'auto') {
                $fetch = $stmt->columnCount() > 1 ? 'query' : 'column';
            }
            if ($fetch == 'query') {
                $result['rows'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $result['total'] = count($result['rows']);
                if ($result['total'] > 0) {
                    $result['header'] = array_keys($result['rows'][0]);
                }
                $stmt = null; // like free_result
            }
            if ($fetch == 'column') {
                $result['rows'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $result['total'] = count($result['rows']);
                $result['header'] = ['column'];
                $stmt = null; // like free_result
            }
            if ($fetch == 'concat') {
                $result['rows'][] = implode(',', $stmt->fetchAll(PDO::FETCH_COLUMN));
                if ($result['rows'][0] == '') {
                    unset($result['rows'][0]);
                }
                $result['total'] = count($result['rows']);
                $result['header'] = ['concat'];
                $stmt = null; // like free_result
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
        return intval($this->link->lastInsertId());
    }

    /**
     * DB Disconnect
     *
     * This function close the database connection and sets the link to null
     */
    public function db_disconnect()
    {
        $this->link = null;
    }
}
