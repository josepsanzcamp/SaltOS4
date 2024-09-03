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

/**
 * PDO MySQL driver
 *
 * This file implements the MySQL improved driver. This is the recommended driver when you want
 * to use MySQL servers, it uses the PDO extension and alow to connect to MySQL servers with a
 * good performance.
 */

/**
 * Database PDO MySQL class
 *
 * This class allow to SaltOS to connect to MySQL databases using the PDO driver
 */
class database_pdo_mysql
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
     * @host => the host for the connection
     * @port => the port used for the connection
     * @name => name of the database for the connection
     * @user => user used to stablish the connection
     * @pass => pass used to stablish the connection
     */
    public function __construct($args)
    {
        if (!class_exists('PDO')) {
            // @codeCoverageIgnoreStart
            show_php_error([
                'phperror' => 'Class PDO not found',
                'details' => 'Try to install php-pdo package',
            ]);
            // @codeCoverageIgnoreEnd
        }
        $args['host'] = $args['host'] ?? 'localhost';
        $args['port'] = $args['port'] ?? 3306;
        $args['name'] = $args['name'] ?? '';
        $args['user'] = $args['user'] ?? '';
        $args['pass'] = $args['pass'] ?? '';
        try {
            $this->link = new PDO(
                'mysql:host=' . $args['host'] . ':' . $args['port'] . ';' .
                'dbname=' . $args['name'],
                $args['user'], $args['pass']
            );
        } catch (PDOException $e) {
            show_php_error(['dberror' => $e->getMessage()]);
        }
        $this->link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db_query("SET NAMES 'utf8mb4'");
        $this->db_query('SET FOREIGN_KEY_CHECKS=0');
        $this->db_query('SET GROUP_CONCAT_MAX_LEN:=@@MAX_ALLOWED_PACKET');
        $this->link->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
    }

    /**
     * DB Check
     *
     * This public function is intended to check that the query execution will not trigger an error
     *
     * @query => the query that you want to validate
     */
    public function db_check($query, $params = null)
    {
        try {
            if (is_array($params)) {
                $stmt = $this->link->prepare($query);
                $stmt->execute($params);
            } else {
                $stmt = $this->link->query($query);
            }
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
        $params = null;
        foreach ($args as $arg) {
            if (is_string($arg)) {
                $fetch = $arg;
            }
            if (is_array($arg)) {
                $params = $arg;
            }
        }
        // CONTINUE
        $query = parse_query($query, 'MYSQL');
        $result = ['total' => 0, 'header' => [], 'rows' => []];
        if (!strlen(trim($query))) {
            return $result;
        }
        // Do the query
        try {
            if (is_array($params)) {
                $stmt = $this->link->prepare($query);
                $stmt->execute($params);
            } else {
                $stmt = $this->link->query($query);
            }
        } catch (PDOException $e) {
            show_php_error(['dberror' => $e->getMessage(), 'query' => $query]);
        }
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
            }
            if ($fetch == 'column') {
                $result['rows'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $result['total'] = count($result['rows']);
                $result['header'] = ['column'];
            }
            if ($fetch == 'concat') {
                if ($row = $stmt->fetch(PDO::FETCH_COLUMN)) {
                    $result['rows'][] = $row;
                }
                while ($row = $stmt->fetch(PDO::FETCH_COLUMN)) {
                    $result['rows'][0] .= ',' . $row;
                }
                $result['total'] = count($result['rows']);
                $result['header'] = ['concat'];
            }
        }
        return $result;
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
