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

/**
 * Database helper module
 *
 * This fie contains useful functions related to database, allow to connect, check queries, execute
 * queries, disconnect, retrieve rows and manipulate resultsets of the database
 */

/**
 * DB Connect
 *
 * This function is intended to stablish the connection to the database
 *
 * @args => is an array with key val pairs
 * @host => the host for the connection
 * @port => the port used for the connection
 * @name => name of the database for the connection
 * @user => user used to stablish the connection
 * @pass => pass used to stablish the connection
 * @file => the file that contains the database
 *
 * Notes:
 *
 * The parameters can be different depending of each database driver, in general the arguments can
 * be the host, port, name, user and pass for database's servers and only file for the sqlite database
 *
 * If the args argument is null, the the function try to use the configuration stored in the config file
 * and depending of the argument presense, it will return the database object or store it in the config
 * to be used by the nexts functions of this php file
 */
function db_connect($args = null)
{
    if ($args === null) {
        $config = get_config('db');
    }
    if ($args !== null) {
        $config = $args;
    }
    $type = $config['type'];
    $php = "php/database/$type.php";
    if (!file_exists($php)) {
        show_php_error(['dberror' => "Database type '$type' not found"]);
    }
    require_once $php;
    $driver = "database_$type";
    if (isset($config[$type])) {
        $config = array_merge(join_attr_value($config[$type]), $config);
        if ($args === null) {
            set_config('db', $config);
        }
    }
    $obj = new $driver($config);
    if ($args === null) {
        set_config('db/obj', $obj);
    }
    if ($args !== null) {
        return $obj;
    }
}

/**
 * DB Check
 *
 * This function is intended to check that the query execution will not trigger an error
 *
 * @query => the query that you want to validate
 */
function db_check($query)
{
    if (!get_config('db/obj')) {
        return false;
    }
    if (!method_exists(get_config('db/obj'), 'db_check')) {
        show_php_error(['dberror' => 'Unknown database connector']);
    }
    return get_config('db/obj')->db_check($query);
}

/**
 * DB Escape
 *
 * This function is intended to escape the special chars to sanitize the string to be used
 * in a sql query
 *
 * @str => the string that you want to sanitize
 */
function db_escape($str)
{
    if (!get_config('db/obj') || !method_exists(get_config('db/obj'), 'db_escape')) {
        show_php_error(['dberror' => 'Unknown database connector']);
    }
    return get_config('db/obj')->db_escape($str);
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
function db_query($query, $fetch = 'query')
{
    if (!get_config('db/obj') || !method_exists(get_config('db/obj'), 'db_query')) {
        show_php_error(['dberror' => 'Unknown database connector']);
    }
    if (
        eval_bool(get_config('debug/patternquerydebug')) &&
        words_exists(get_config('debug/patternquerywords'), $query)
    ) {
        file_put_contents(get_config('debug/patternqueryoutput'), $query, FILE_APPEND);
        chmod_protected(get_config('debug/patternqueryoutput'), 0666);
    }
    if (eval_bool(get_config('debug/slowquerydebug'))) {
        $curtime = microtime(true);
    }
    $result = get_config('db/obj')->db_query($query, $fetch);
    if (eval_bool(get_config('debug/slowquerydebug'))) {
        $curtime = microtime(true) - $curtime;
        $maxtime = get_config('debug/slowquerytime');
        if ($curtime > $maxtime) {
            addtrace([
                'dbwarning' => "Slow query requires $curtime seconds",
                'query' => $query,
            ], get_config('debug/dbwarningfile') ?? 'dbwarning.log');
        }
    }
    return $result;
}

/**
 * DB Fetch Row
 *
 * This function returns the next row of the resultset queue
 *
 * @result => this argument is passed by reference and contains the resultset queue
 *            obtained by the db_query
 */
function db_fetch_row(&$result)
{
    if (!isset($result['__array_reverse__'])) {
        $result['rows'] = array_reverse($result['rows']);
        $result['__array_reverse__'] = 1;
    }
    return array_pop($result['rows']);
}

/**
 * DB Fetch All
 *
 * This function returns all rows of the resultset queue
 *
 * @result => this argument is passed by reference and contains the resultset queue
 *            obtained by the db_query
 */
function db_fetch_all(&$result)
{
    return $result['rows'];
}

/**
 * DB Num Rows
 *
 * This function returns the total number of the results in the resultset queue
 *
 * @result => this argument is passed by reference and contains the resultset queue
 *            obtained by the db_query
 */
function db_num_rows($result)
{
    return $result['total'];
}

/**
 * DB Num Fields
 *
 * This function returns the number of fields of the resultset queue
 *
 * @result => this argument is passed by reference and contains the resultset queue
 *            obtained by the db_query
 */
function db_num_fields($result)
{
    return count($result['header']);
}

/**
 * DB Field Name
 *
 * This function returns the name of the field identified by the index field
 *
 * @result => this argument is passed by reference and contains the resultset queue
 *            obtained by the db_query
 */
function db_field_name($result, $index)
{
    if (!isset($result['header'][$index])) {
        show_php_error(['dberror' => "Unknown field name at position {$index}"]);
    }
    return $result['header'][$index];
}

/**
 * DB Free
 *
 * This function releases all memory used by the resultset queue
 *
 * @result => this argument is passed by reference and contains the resultset queue
 *            obtained by the db_query
 */
function db_free(&$result)
{
    $result = ['total' => 0, 'header' => [], 'rows' => []];
}

/**
 * DB Disconnect
 *
 * This function close the database connection and sets the link to null
 */
function db_disconnect()
{
    if (!get_config('db/obj') || !method_exists(get_config('db/obj'), 'db_disconnect')) {
        show_php_error(['dberror' => 'Unknown database connector']);
    }
    get_config('db/obj')->db_disconnect();
    set_config('db/obj', null);
}
