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
 * SQL utils helper module
 *
 * This fie contains useful functions related to SQL queries, allow to help modules that requires
 * the entire management of the database (create tables, drop tables, create indexes, and more),
 * too allow to prepare sql queries suck inserts, updates or wheres fragments that are procected
 * to external injections, for example, by escaping all special characters.
 *
 * Too it provides functions to do subparts of the where queries suck as special likes combinations
 * or match again combinations for the fulltext search engine, see all detailed information by
 * reading the list of functions of this module
 */

/**
 * Parse Query
 *
 * This function is intended to apply the query filters defined by the users
 * when write queries for multiples db engines as MySQL and/or SQLite, for
 * example, if you want to write a fragment of SQL with one version for MySQL
 * and another version for SQLite, you can do / *MYSQL ... * // *SQLite ... * /
 *
 * Note that the previous example add a spaces between the bar and the asterisc
 * because we can not put comments inside another comment!!!
 *
 * @query => the query that must be parsed
 * @type  => the db type that you want to allow by the filters
 */
function parse_query($query, $type = "")
{
    if ($type == "") {
        $type = __parse_query_type();
    }
    $pos = __parse_query_strpos($query, "/*");
    $len = strlen($type);
    while ($pos !== false) {
        $pos2 = __parse_query_strpos($query, "*/", $pos + 2);
        if ($pos2 !== false) {
            $pos3 = __parse_query_strpos($query, "/*", $pos + 2);
            while ($pos3 !== false && $pos3 < $pos2) {
                $pos = $pos3;
                $pos3 = __parse_query_strpos($query, "/*", $pos + 2);
            }
            if (substr($query, $pos + 2, $len) == $type) {
                $query = substr($query, 0, $pos) .
                    trim(substr($query, $pos + 2 + $len, $pos2 - $pos - 2 - $len)) .
                    substr($query, $pos2 + 2);
            } else {
                $query = substr($query, 0, $pos) . substr($query, $pos2 + 2);
            }
            $pos = __parse_query_strpos($query, "/*", $pos);
        } else {
            $pos = __parse_query_strpos($query, "/*", $pos + 2);
        }
    }
    return $query;
}

/**
 * Parse Query Type helper
 *
 * This function returns the type used by parse_query using as detector the
 * dbtype of the config file, currently only allow to return MYSQL and/or SQLITE
 */
function __parse_query_type()
{
    switch (get_config("db/type")) {
        case "pdo_sqlite":
        case "sqlite3":
            return "SQLITE";
        case "pdo_mysql":
        case "mysqli":
            return "MYSQL";
        default:
            show_php_error(["phperror" => "Unknown type '" . get_config("db/type") . "'"]);
    }
}

/**
 * Parse Query Strpos helper
 *
 * This function is the same that strpos, but with some improvements required
 * by the parse_query funcion, the idea is to use the strpos functionality, but
 * controlling that the found position must acomplish some constraints as the
 * number of simple and double quotes must to be even
 *
 * The arguments are the same that the strpos function
 *
 * @haystack => string where search the needle
 * @needle   => the needle text that must be found in the haystack
 * @offset   => bias applied to begin the search of the needle
 */
function __parse_query_strpos($haystack, $needle, $offset = 0)
{
    $len = strlen($needle);
    $pos = strpos($haystack, $needle, $offset);
    if ($pos !== false) {
        $len2 = $pos - $offset;
        if ($len2 > 0) {
            $count1 = substr_count($haystack, "'", $offset, $len2) -
                      substr_count($haystack, "\\'", $offset, $len2);
            $count2 = substr_count($haystack, '"', $offset, $len2) -
                      substr_count($haystack, '\\"', $offset, $len2);
            while ($pos !== false && ($count1 % 2 != 0 || $count2 % 2 != 0)) {
                $offset = $pos + $len;
                $pos = strpos($haystack, $needle, $offset);
                if ($pos !== false) {
                    $len2 = $pos - $offset;
                    if ($len2 > 0) {
                        $count1 +=
                            substr_count($haystack, "'", $offset, $len2) -
                            substr_count($haystack, "\\'", $offset, $len2);
                        $count2 +=
                            substr_count($haystack, '"', $offset, $len2) -
                            substr_count($haystack, '\\"', $offset, $len2);
                    }
                }
            }
        }
    }
    return $pos;
}

/**
 * Execute Query
 *
 * This function executes the query and depending in the result, returns the
 * resultset trying to do the more good combination in the return data
 *
 * @query => the SQL query that you want to execute
 *
 * Note that the db_query is executed with the "auto" fetch mode, this causes
 * that the db_query returns an array with one dimension if the query only
 * generates a resultset with only one column, or returns an array with two
 * dimensions if the query generates a resultest with more that one column
 *
 * To be more practice:
 *
 * If you execute a query that select one field and only returns one row,
 * the return value will be the value of the field
 *
 * If you execute a query that select one field and returns more that one
 * row, the return value will be an array of one dimension with all values
 * of this field
 *
 * If you execute a query that select multiples fields and only return one
 * row, the return value will be an array of one dimension with all fields
 *
 * Ig you execute a query that select multiples fields and returns more that
 * one row, the return value will be an array of two dimensions with all rows
 * and each row with all fields
 *
 * Be carefully to use the output of this command in an foreach, for example
 * because you can get for the same query differents output types, if you
 * need to be more standarized in the output types, see the execute_query_array
 */
function execute_query($query)
{
    $result = db_query($query, "auto");
    $numrows = db_num_rows($result);
    $numfields = db_num_fields($result);
    $value = null;
    if ($numrows == 1 && $numfields == 1) {
        $value = db_fetch_row($result);
    } elseif ($numrows == 1 && $numfields > 1) {
        $value = db_fetch_row($result);
    } elseif ($numrows > 1 && $numfields == 1) {
        $value = db_fetch_all($result);
    } elseif ($numrows > 1 && $numfields > 1) {
        $value = db_fetch_all($result);
    }
    db_free($result);
    return $value;
}

/**
 * Execute Query Array
 *
 * This function is the same that execute_query but guarantee that for the
 * same query, you get the same output type if the resultet contains one
 * row or more rows, useful is you want to use the output of this function
 * in a foreach, for example
 *
 * @query => the SQL query that you want to execute
 */
function execute_query_array($query)
{
    $result = db_query($query, "auto");
    $rows = db_fetch_all($result);
    db_free($result);
    return $rows;
}

/**
 * Get Fields
 *
 * This function returns the fields of the requested table
 *
 * @table => the table where that you want to know the fields
 */
function get_fields($table)
{
    $query = "/*MYSQL SHOW COLUMNS FROM $table *//*SQLITE PRAGMA TABLE_INFO($table) */";
    $result = db_query($query);
    $fields = [];
    while ($row = db_fetch_row($result)) {
        if (isset($row["Field"])) {
            $fields[] = ["name" => $row["Field"], "type" => strtoupper($row["Type"])];
        }
        if (isset($row["name"])) {
            $fields[] = ["name" => $row["name"], "type" => strtoupper($row["type"])];
        }
    }
    db_free($result);
    return $fields;
}

/**
 * Get Indexes
 *
 * This function returns the indexes of the requested table
 *
 * @table => the table where that you want to know the indexes
 */
function get_indexes($table)
{
    $indexes = [];
    // FOR SQLITE
    $query = "/*SQLITE PRAGMA INDEX_LIST($table) */";
    $result = db_query($query);
    while ($row = db_fetch_row($result)) {
        $index = $row["name"];
        $query2 = "/*SQLITE PRAGMA INDEX_INFO($index) */";
        $result2 = db_query($query2);
        $fields = [];
        while ($row2 = db_fetch_row($result2)) {
            $fields[] = $row2["name"];
        }
        db_free($result2);
        $indexes[$index] = $fields;
    }
    db_free($result);
    // FOR MYSQL
    $query = "/*MYSQL SHOW INDEXES FROM $table */";
    $result = db_query($query);
    while ($row = db_fetch_row($result)) {
        $index = $row["Key_name"];
        $column = $row["Column_name"];
        $where = 1;
        if ($index == "PRIMARY") {
            $where = 0;
        }
        if ($where) {
            if (!isset($indexes[$index])) {
                $indexes[$index] = [];
            }
            $indexes[$index][] = $column;
        }
    }
    return $indexes;
}

/**
 * Get Tables
 *
 * This function returns the tables of the database
 */
function get_tables()
{
    $query = "/*MYSQL SHOW TABLES *//*SQLITE SELECT name
        FROM sqlite_master
        WHERE type='table'
            AND name NOT LIKE 'sqlite_%' */";
    $result = db_query($query);
    $tables = [];
    while ($row = db_fetch_row($result)) {
        $row = array_values($row);
        $tables[] = $row[0];
    }
    db_free($result);
    return $tables;
}

/**
 * Get Field Type
 *
 * This function returns an standarized type for the specific types used in
 * the real database, for example, returns string if the field is of TEXT type
 *
 * @type => the real type in the database
 */
function get_field_type($type)
{
    $type = parse_query($type);
    $type1 = strtoupper(strtok($type, "("));
    static $datatypes = null;
    if ($datatypes === null) {
        $temp = [
            "int" => "TINYINT,SMALLINT,MEDIUMINT,INT,BIGINT,INTEGER",
            "string" => "TINYTEXT,TEXT,MEDIUMTEXT,LONGTEXT,VARCHAR",
            "float" => "DECIMAL,NUMERIC,FLOAT,REAL,DOUBLE",
            "date" => "DATE",
            "time" => "TIME",
            "datetime" => "DATETIME",
        ];
        $datatypes = [];
        foreach ($temp as $key => $val) {
            $val = explode(",", $val);
            foreach ($val as $key2 => $val2) {
                $datatypes[$val2] = $key;
            }
        }
    }
    if (isset($datatypes[$type1])) {
        return $datatypes[$type1];
    }
    show_php_error(["phperror" => "Unknown type '$type1' in " . __FUNCTION__]);
}

/**
 * Get Field Size
 *
 * This function returns the size for the types used in the database, for
 * example, returns 65535 if the field is of TEXT type
 *
 * @type => the real type in the database
 */
function get_field_size($type)
{
    $type = parse_query($type);
    $type1 = strtoupper(strtok($type, "("));
    $type2 = strtok(")");
    $datasizes = [
        "TINYTEXT" => 255,
        "TEXT" => 65535,
        "MEDIUMTEXT" => 16777215,
        "LONGTEXT" => 4294967295,
    ];
    if (isset($datasizes[$type1])) {
        return intval($datasizes[$type1]);
    }
    if ($type2 != "") {
        return intval($type2);
    }
    show_php_error(["phperror" => "Unknown type '$type1' in " . __FUNCTION__]);
}

/**
 * SQL Create Table
 *
 * This function returns the SQL needed to create the table defined in the
 * tablespec argument
 *
 * @tablespec => the specification for the create table, see the dbschema
 *               file to understand the tablespec structure
 *
 * This function creates the table, supports the primary key, supports the
 * foreign key, and detect fulltext indexes with mroonga engines
 */
function sql_create_table($tablespec)
{
    $table = $tablespec["#attr"]["name"];
    $fields = [];
    foreach ($tablespec["value"]["fields"] as $field) {
        $name = $field["#attr"]["name"];
        $type = $field["#attr"]["type"];
        $type2 = get_field_type($type);
        if ($type2 == "int") {
            $def = intval(0);
        } elseif ($type2 == "float") {
            $def = floatval(0);
        } elseif ($type2 == "date") {
            $def = dateval(0);
        } elseif ($type2 == "time") {
            $def = timeval(0);
        } elseif ($type2 == "datetime") {
            $def = datetimeval(0);
        } elseif ($type2 == "string") {
            $def = "";
        } else {
            show_php_error(["phperror" => "Unknown type '$type' in " . __FUNCTION__]);
        }
        $extra = "NOT NULL DEFAULT '$def'";
        if (isset($field["#attr"]["pkey"]) && eval_bool($field["#attr"]["pkey"])) {
            $extra = "PRIMARY KEY /*MYSQL AUTO_INCREMENT *//*SQLITE AUTOINCREMENT */";
        }
        $name2 = escape_reserved_word($name);
        $fields[] = "$name2 $type $extra";
    }
    foreach ($tablespec["value"]["fields"] as $field) {
        if (isset($field["#attr"]["fkey"])) {
            $fkey = $field["#attr"]["fkey"];
            if ($fkey != "") {
                $name = $field["#attr"]["name"];
                $fields[] = "FOREIGN KEY ($name) REFERENCES $fkey (id)";
            }
        }
    }
    $fields = implode(",", $fields);
    if (in_array($table, get_fulltext_from_dbschema()) && __has_engine("mroonga")) {
        $post = "/*MYSQL ENGINE=Mroonga CHARSET=utf8mb4 */";
    } elseif (__has_engine("aria")) {
        $post = "/*MYSQL ENGINE=Aria CHARSET=utf8mb4 */";
    } else {
        $post = "/*MYSQL ENGINE=MyISAM CHARSET=utf8mb4 */";
    }
    $query = "CREATE TABLE $table ($fields) $post";
    return $query;
}

/**
 * Has Engine
 *
 * This function allow to SaltOS to ask to the database if an enxine is
 * availabie
 *
 * @engine => the engine that you want to get information about existence
 */
function __has_engine($engine)
{
    static $engines = null;
    if ($engines === null) {
        $engines = [];
        if (get_config("db/obj")) {
            $query = "/*MYSQL SHOW ENGINES */";
            $result = db_query($query);
            while ($row = db_fetch_row($result)) {
                $row = array_values($row);
                $temp = strtolower($row[0]);
                $engines[$temp] = $temp;
            }
            db_free($result);
        }
    }
    return isset($engines[strtolower($engine)]);
}

/**
 * SQL Alter Table
 *
 * This function returns the alter table command
 *
 * @orig => source table
 * @dest => destination table
 */
function sql_alter_table($orig, $dest)
{
    $query = "ALTER TABLE $orig RENAME TO $dest";
    return $query;
}

/**
 * SQL Insert From Select
 *
 * This function returns the insert from select command
 *
 * @orig => source table
 * @dest => destination table
 */
function sql_insert_from_select($dest, $orig)
{
    $fdest = get_fields($dest);
    $ldest = [];
    foreach ($fdest as $f) {
        $ldest[] = $f["name"];
    }
    $forig = get_fields($orig);
    $lorig = [];
    foreach ($forig as $f) {
        $lorig[] = $f["name"];
    }
    $defs = [];
    foreach ($fdest as $f) {
        $type = $f["type"];
        $type2 = get_field_type($type);
        if ($type2 == "int") {
            $defs[] = intval(0);
        } elseif ($type2 == "float") {
            $defs[] = floatval(0);
        } elseif ($type2 == "date") {
            $defs[] = dateval(0);
        } elseif ($type2 == "time") {
            $defs[] = timeval(0);
        } elseif ($type2 == "datetime") {
            $defs[] = datetimeval(0);
        } elseif ($type2 == "string") {
            $defs[] = "";
        } else {
            show_php_error(["phperror" => "Unknown type '$type' in " . __FUNCTION__]);
        }
    }
    $keys = [];
    $vals = [];
    foreach ($ldest as $key => $l) {
        $def = $defs[$key];
        $l2 = escape_reserved_word($l);
        $keys[] = $l2;
        $vals[] = in_array($l, $lorig) ? $l2 : "'$def'";
    }
    $keys = implode(",", $keys);
    $vals = implode(",", $vals);
    $query = "INSERT INTO $dest($keys) SELECT $vals FROM $orig";
    return $query;
}

/**
 * SQL Drop Table
 *
 * This function returns the drop table command
 *
 * @table => table that you want to drop
 */
function sql_drop_table($table)
{
    $query = "DROP TABLE $table";
    return $query;
}

/**
 * SQL Create Index
 *
 * This function returns the SQL needed to create the index defined in the
 * indexspec argument
 *
 * @indexspec => the specification for the create index, see the dbschema
 *               file to understand the indexspec structure
 *
 * This function creates the index, supports fulltext indexes
 */
function sql_create_index($indexspec)
{
    $name = $indexspec["#attr"]["name"];
    $table = $indexspec["#attr"]["table"];
    $fields = $indexspec["#attr"]["fields"];
    $fields = explode(",", $fields);
    foreach ($fields as $key => $val) {
        $fields[$key] = escape_reserved_word($val);
    }
    $fields = implode(",", $fields);
    if (isset($indexspec["#attr"]["fulltext"]) && eval_bool($indexspec["#attr"]["fulltext"])) {
        $pre = "/*MYSQL FULLTEXT */";
    } else {
        $pre = "";
    }
    $query = "CREATE $pre INDEX $name ON $table ($fields)";
    return $query;
}

/**
 * SQL Drop Index
 *
 * This function returns the drop index command
 *
 * @index => index that you want to drop
 * @table => table where the indes is part of
 */
function sql_drop_index($index, $table)
{
    $query = "/*MYSQL DROP INDEX $index ON $table *//*SQLITE DROP INDEX $index */";
    return $query;
}

/**
 * Make Insert Query
 *
 * Returns the insert query for the table with all fields specified by the
 * array param
 *
 * @table => table where you want to add the register
 * @array => array with key val pairs that represent the field and the value
 *           of the field
 *
 * Notes:
 *
 * This function tries to cast each value to their data type getting this
 * information from dbschema config, you can pass in array all fields that
 * you want and not is needed to put all fields of the table, only the
 * fields that appear in the array will be used in the insert, if some
 * field is not a part of the fields of the table, an error will be
 * triggered
 *
 * This function uses the array_key_exists instead of isset because the
 * check of the $array[$name] fails when the item exists but is false or
 * null, for example
 */
function make_insert_query($table, $array)
{
    $fields = get_fields_from_dbschema($table);
    if (!count($fields)) {
        show_php_error(["phperror" => "Unknown fields in " . __FUNCTION__]);
    }
    $list1 = [];
    $list2 = [];
    foreach ($fields as $field) {
        $name = $field["name"];
        if (!array_key_exists($name, $array)) {
            continue;
        }
        $type = $field["type"];
        $type2 = get_field_type($type);
        if ($type2 == "int") {
            $temp = intval($array[$name]);
        } elseif ($type2 == "float") {
            $temp = floatval($array[$name]);
        } elseif ($type2 == "date") {
            $temp = dateval($array[$name]);
        } elseif ($type2 == "time") {
            $temp = timeval($array[$name]);
        } elseif ($type2 == "datetime") {
            $temp = datetimeval($array[$name]);
        } elseif ($type2 == "string") {
            $size2 = get_field_size($type);
            $temp = addslashes(substr(strval($array[$name]), 0, $size2));
        } else {
            show_php_error(["phperror" => "Unknown type '$type' in " . __FUNCTION__]);
        }
        $name2 = escape_reserved_word($name);
        $list1[] = $name2;
        $list2[] = "'$temp'";
        unset($array[$name]);
    }
    if (count($array)) {
        $temp = implode(", ", array_keys($array));
        show_php_error(["phperror" => "Unused data '$temp' in " . __FUNCTION__]);
    }
    $list1 = implode(",", $list1);
    $list2 = implode(",", $list2);
    $query = "INSERT INTO $table($list1) VALUES($list2)";
    return $query;
}

/**
 * Make Update Query
 *
 * Returns the update query for the table with all fields specified by the
 * array param and using the specified where
 *
 * @table => table where you want to update the register
 * @array => array with key val pairs that represent the field and the value of
 *           the field
 * @where => where clausule used to update only the expected registers, can be
 *           the output of make_where_query
 *
 * Notes:
 *
 * This function tries to cast each value to their data type getting this
 * information from dbschema config, you can pass in array all fields that
 * you want and not is needed to put all fields of the table, only the
 * fields that appear in the array will be used in the update, if some
 * field is not a part of the fields of the table, an error will be
 * triggered
 *
 * This function uses the array_key_exists instead of isset because the
 * check of the $array[$name] fails when the item exists but is false or
 * null, for example
 */
function make_update_query($table, $array, $where)
{
    $fields = get_fields_from_dbschema($table);
    if (!count($fields)) {
        show_php_error(["phperror" => "Unknown fields in " . __FUNCTION__]);
    }
    $list = [];
    foreach ($fields as $field) {
        $name = $field["name"];
        if (!array_key_exists($name, $array)) {
            continue;
        }
        $type = $field["type"];
        $type2 = get_field_type($type);
        if ($type2 == "int") {
            $temp = intval($array[$name]);
        } elseif ($type2 == "float") {
            $temp = floatval($array[$name]);
        } elseif ($type2 == "date") {
            $temp = dateval($array[$name]);
        } elseif ($type2 == "time") {
            $temp = timeval($array[$name]);
        } elseif ($type2 == "datetime") {
            $temp = datetimeval($array[$name]);
        } elseif ($type2 == "string") {
            $size2 = get_field_size($type);
            $temp = addslashes(substr(strval($array[$name]), 0, $size2));
        } else {
            show_php_error(["phperror" => "Unknown type '$type' in " . __FUNCTION__]);
        }
        $name2 = escape_reserved_word($name);
        $list[] = "$name2='$temp'";
        unset($array[$name]);
    }
    if (count($array)) {
        $temp = implode(", ", array_keys($array));
        show_php_error(["phperror" => "Unused data '$temp' in " . __FUNCTION__]);
    }
    $list = implode(",", $list);
    $query = "UPDATE $table SET $list WHERE $where";
    return $query;
}

/**
 * Make Where Query
 *
 * This function allow to create where sentences joinin all fields by AND
 *
 * @array => array with key val pairs that represent the field and the value of
 *           the field
 *
 * Notes:
 *
 * The keys normally contains the name of the field, but if you need to use
 * a different comparison operator, you can use the field name and add the
 * operator that you want to use in the comparison, the allowed comparison
 * operators are >, <, =, >=, <=, !=
 */
function make_where_query($array)
{
    $list = [];
    foreach ($array as $key => $val) {
        if (in_array(substr($key, -2, 2), [">=", "<=", "!="])) {
            $cmp = "";
        } elseif (in_array(substr($key, -1, 1), [">", "<", "="])) {
            $cmp = "";
        } else {
            $cmp = "=";
        }
        $key2 = escape_reserved_word($key);
        $list[] = $key2 . $cmp . "'" . addslashes(strval($val)) . "'";
    }
    $query = "(" . implode(" AND ", $list) . ")";
    return $query;
}

/**
 * Escape Reserved Word
 *
 * This function tries to escape the reserved words that can not be used
 * in sql queries as field names or table names, currently is only used
 * to escape field names but in a future, if it is needed, can be added
 * to escape table names too
 *
 * @word => the word that must to be escape if needed
 */
function escape_reserved_word($word)
{
    if (in_array($word, ["key", "table", "from", "to"])) {
        return "`$word`";
    }
    return $word;
}

/**
 * Make Like Query
 *
 * This function is intended to returns the sql fragment to be added to the
 * where condition to filter for the specified keys and values
 *
 * @keys    => an string with comma separated field names
 * @values  => the value of the input search
 * @minsize => the minimal size of the length used in each like
 * @default => sql fraement returned if some thing was wrong
 *
 * Notes:
 *
 * This function generates a sequence of (like or like) and (like and like)
 * and is able to understand the prefix plus or minus in each word of the
 * search string, this allow to the function to use the like or not like
 * depending the sign of the word, and too to use the disjunction or
 * conjunction in each like group
 */
function make_like_query($keys, $values, $args = [])
{
    // Process args
    $minsize = $args["minsize"] ?? 3;
    $default = $args["default"] ?? "1=0";
    // Continue
    $keys = explode(",", $keys);
    foreach ($keys as $key => $val) {
        $val = trim($val);
        if ($val != "") {
            $keys[$key] = $val;
        } else {
            unset($keys[$key]);
        }
    }
    if (!count($keys)) {
        return $default;
    }
    $values = explode(" ", encode_bad_chars($values, " ", "+-"));
    $types = [];
    foreach ($values as $key => $val) {
        $types[$key] = "+";
        while (isset($val[0]) && in_array($val[0], ["+", "-"])) {
            $types[$key] = $val[0];
            $val = substr($val, 1);
        }
        if (strlen($val) >= $minsize) {
            $values[$key] = $val;
        } else {
            unset($values[$key]);
        }
    }
    if (!count($values)) {
        return $default;
    }
    $query = [];
    foreach ($values as $key => $val) {
        if ($types[$key] == "+") {
            $query2 = [];
            foreach ($keys as $key2) {
                $query2[] = "$key2 LIKE '%$val%'";
            }
            $query[] = "(" . implode(" OR ", $query2) . ")";
        } else {
            $query2 = [];
            foreach ($keys as $key2) {
                $query2[] = "$key2 NOT LIKE '%$val%'";
            }
            $query[] = "(" . implode(" AND ", $query2) . ")";
        }
    }
    $query = "(" . implode(" AND ", $query) . ")";
    return $query;
}

/**
 * Make Fulltext Query Helper
 *
 * This function is similar to the make_like_query, but uses the match agains
 * clausule instead of the like clausule, the match agaings is used for
 * fulltext searches and generally, this function is not intended to be used
 * directly, it must acts as a helper of the make_fulltext_query
 *
 * @values  => the value of the input search
 * @minsize => the minimal size of the length used in each like
 * @default => sql fraement returned if some thing was wrong
 *
 * Notes:
 *
 * This function differs between the make_like_query in the idea that this
 * function only is used to search using fulltext indexes and in one unique
 * field named search
 */
function __make_fulltext_query_helper($values, $args = [])
{
    // Process args
    $minsize = $args["minsize"] ?? 3;
    $default = $args["default"] ?? "1=0";
    // Continue
    $values = explode(" ", encode_bad_chars($values, " ", "+-"));
    foreach ($values as $key => $val) {
        $type = "+";
        while (isset($val[0]) && in_array($val[0], ["+", "-"])) {
            $type = $val[0];
            $val = substr($val, 1);
        }
        if (strlen($val) >= $minsize) {
            $values[$key] = $type . '"' . $val . '"';
        } else {
            unset($values[$key]);
        }
    }
    if (!count($values)) {
        return $default;
    }
    $query = "MATCH(search) AGAINST('+(" . implode(" ", $values) . ")' IN BOOLEAN MODE)";
    return $query;
}

/*
 * Get Engine
 *
 * This function returns the engine of the table, intended to detect the
 * mroonga storage engine
 *
 * @table => the table to retrieve the engine
 */
function get_engine($table)
{
    $query = "/*MYSQL SHOW TABLE STATUS WHERE Name='$table' */";
    $result = db_query($query);
    $engine = "";
    while ($row = db_fetch_row($result)) {
        $engine = $row["Engine"];
    }
    db_free($result);
    return $engine;
}

/**
 * Make Fulltext Query
 *
 * While the two version returns the fragment that must to be added to the
 * query that search in the table that contains the search field, this function
 * allow to specify the same that the two version with two fields more, the
 * app and the prefix to be added to the id field of the in subquery
 *
 * @values  => the value of the input search
 * @app     => the app used to detect the indexing table
 * @prefix  => the prefix added to the id used in the in subquery
 * @minsize => the minimal size of the length used in each like
 * @default => sql fraement returned if some thing was wrong
 */
function make_fulltext_query($values, $app, $args = [])
{
    // Process args
    $prefix = $args["prefix"] ?? "";
    $minsize = $args["minsize"] ?? 3;
    $default = $args["default"] ?? "1=0";
    // Continue
    $table = app2table($app);
    $engine = strtolower(get_engine("{$table}_index"));
    if ($engine == "mroonga") {
        $where = __make_fulltext_query_helper($values, $args);
    } else {
        $where = make_like_query("search", $values, $args);
    }
    if ($where == $default) {
        return $where;
    }
    $query = "{$prefix}id IN (SELECT id FROM {$table}_index WHERE $where)";
    return $query;
}
