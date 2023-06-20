<?php

/**
 ____        _ _    ___  ____    _  _    ___
/ ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
\___ \ / _` | | __| | | \___ \  | || |_| | | |
 ___) | (_| | | |_| |_| |___) | |__   _| |_| |
|____/ \__,_|_|\__|\___/|____/     |_|(_)___/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2023 by Josep Sanz Campderrós
More information in https://www.saltos.org or info@saltos.org

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

declare(strict_types=1);

// phpcs:disable Generic.Files.LineLength

/**
 * DB Schema
 *
 * This function try to maintain the database structure, to do it, this feature uses the dbschema.xml
 * file to store the database structure.
 */
function db_schema()
{
    $hash1 = get_config("xml/dbschema.xml");
    $hash2 = md5(serialize(array(xmlfile2array("xml/dbschema.xml"),xmlfile2array("xml/dbstatic.xml"))));
    if ($hash1 == $hash2) {
        return;
    }
    if (!semaphore_acquire(array("db_schema","db_static"), get_default("semaphoretimeout", 100000))) {
        return;
    }
    $dbschema = eval_attr(xmlfile2array("xml/dbschema.xml"));
    $dbschema = __dbschema_auto_apps($dbschema);
    $dbschema = __dbschema_auto_fkey($dbschema);
    $dbschema = __dbschema_auto_name($dbschema);
    if (is_array($dbschema) && isset($dbschema["tables"]) && is_array($dbschema["tables"])) {
        $tables1 = get_tables();
        $tables2 = get_tables_from_dbschema();
        $ignores = get_ignores_from_dbschema();
        foreach ($ignores as $ignore) {
            foreach ($tables1 as $key => $val) {
                if ($ignore == $val) {
                    unset($tables1[$key]);
                }
            }
            foreach ($tables2 as $key => $val) {
                if ($ignore == $val) {
                    unset($tables2[$key]);
                }
            }
        }
        foreach ($tables1 as $table) {
            $isbackup = (substr($table, 0, 2) == "__" && substr($table, -2, 2) == "__");
            if (!$isbackup && !in_array($table, $tables2)) {
                $backup = "__{$table}__";
                db_query(sql_alter_table($table, $backup));
            }
        }
        foreach ($dbschema["tables"] as $tablespec) {
            $table = $tablespec["#attr"]["name"];
            if (!in_array($table, $tables2)) {
                continue;
            }
            $backup = "__{$table}__";
            if (in_array($table, $tables1)) {
                $fields1 = get_fields($table);
                $fields2 = get_fields_from_dbschema($table);
                $hash3 = md5(serialize($fields1));
                $hash4 = md5(serialize($fields2));
                if ($hash3 != $hash4) {
                    db_query(sql_alter_table($table, $backup));
                    db_query(sql_create_table($tablespec));
                    db_query(sql_insert_from_select($table, $backup));
                    db_query(sql_drop_table($backup));
                }
            } elseif (in_array($backup, $tables1)) {
                $fields1 = get_fields($backup);
                $fields2 = get_fields_from_dbschema($table);
                $hash3 = md5(serialize($fields1));
                $hash4 = md5(serialize($fields2));
                if ($hash3 != $hash4) {
                    db_query(sql_create_table($tablespec));
                    db_query(sql_insert_from_select($table, $backup));
                    db_query(sql_drop_table($backup));
                } else {
                    db_query(sql_alter_table($backup, $table));
                }
            } else {
                db_query(sql_create_table($tablespec));
            }
            $indexes1 = get_indexes($table);
            $indexes2 = get_indexes_from_dbschema($table);
            foreach ($indexes1 as $index => $fields) {
                if (!array_key_exists($index, $indexes2)) {
                    db_query(sql_drop_index($index, $table));
                }
            }
            if (isset($tablespec["value"]["indexes"]) && is_array($tablespec["value"]["indexes"])) {
                foreach ($tablespec["value"]["indexes"] as $indexspec) {
                    $indexspec["#attr"]["table"] = $table;
                    // This parse_query is important because the name of the index is different
                    // for MySQL and SQLite and must to be parsed
                    $index = parse_query($indexspec["#attr"]["name"]);
                    if (array_key_exists($index, $indexes1)) {
                        $fields1 = $indexes1[$index];
                        $fields2 = $indexes2[$index];
                        $hash3 = md5(serialize($fields1));
                        $hash4 = md5(serialize($fields2));
                        if ($hash3 != $hash4) {
                            db_query(sql_drop_index($index, $table));
                            db_query(sql_create_index($indexspec));
                        }
                    } else {
                        db_query(sql_create_index($indexspec));
                    }
                }
            }
        }
    }
    set_config("xml/dbschema.xml", $hash2);
    semaphore_release(array("db_schema","db_static"));
}

/**
 * DB Static
 *
 * This function try to maintain the database contents, to do it, this feature uses the dbstatic.xml
 * file to store the database contents that must to be maintaned.
 *
 * This new version of the db_static allow to specify if you want to delete all contents of the table
 * and too, allow you to use a comma separated values in fields as "id", start by "id_" or end by "_id"
 */
function db_static()
{
    $hash1 = get_config("xml/dbstatic.xml");
    $hash2 = md5(serialize(xmlfile2array("xml/dbstatic.xml")));
    if ($hash1 == $hash2) {
        return;
    }
    if (!semaphore_acquire(array("db_schema","db_static"), get_default("semaphoretimeout", 100000))) {
        return;
    }
    $dbstatic = eval_attr(xmlfile2array("xml/dbstatic.xml"));
    if (is_array($dbstatic) && isset($dbstatic["tables"]) && is_array($dbstatic["tables"])) {
        foreach ($dbstatic["tables"] as $data) {
            $table = $data["#attr"]["name"];
            $delete = true;
            if (isset($data["#attr"]["delete"])) {
                $delete =  eval_bool($data["#attr"]["delete"]);
            }
            $rows = $data["value"];
            if ($delete) {
                $query = "DELETE FROM $table";
                db_query($query);
            }
            foreach ($rows as $row) {
                __dbstatic_insert($table, $row["#attr"], $delete);
            }
        }
    }
    set_config("xml/dbstatic.xml", $hash2);
    semaphore_release(array("db_schema","db_static"));
}

/**
 * DB Static insert
 *
 * This function is a helper of previous function, is intended to be used by db_static and
 * allow to use a comma separated values in fields as "id", start by "id_" or end by "_id"
 *
 * @table => the table that you want to use in the insert process
 * @row => the row that you want to add in the table
 * @delete => this field allow to check if the row exists to do an update instead of insert
 */
function __dbstatic_insert($table, $row, $delete)
{
    $found = "";
    foreach ($row as $field => $value) {
        if ($field == "id" || substr($field, 0, 3) == "id_" || substr($field, -3, 3) == "_id") {
            if (strpos($value, ",") !== false) {
                $found = $field;
                break;
            }
        }
    }
    if ($found != "") {
        $a = explode(",", $row[$found]);
        foreach ($a as $b) {
            $row[$found] = $b;
            __dbstatic_insert($table, $row, $delete);
        }
    } else {
        $query = make_insert_query($table, $row);
        if (!$delete) {
            $where = make_where_query(array("id" => $row["id"]));
            $query2 = "SELECT id FROM $table WHERE $where";
            $exists = execute_query($query2);
            if ($exists) {
                $query = make_update_query($table, $row, $where);
            }
        }
        db_query($query);
    }
}

/**
 * Get Tables from DB Schema
 *
 * This function returns the tables from the DB Schema file
 */
function get_tables_from_dbschema()
{
    return __dbschema_helper(__FUNCTION__, "");
}

/**
 * Get Fields from DB Schema
 *
 * This function returns the fields from the DB Schema file
 *
 * @table => the table that you want to request the fields
 */
function get_fields_from_dbschema($table)
{
    return __dbschema_helper(__FUNCTION__, $table);
}

/**
 * Get Indexes from DB Schema
 *
 * This function returns the indexes from the DB Schema file
 *
 * @table => the table that you want to request the indexes
 */
function get_indexes_from_dbschema($table)
{
    return __dbschema_helper(__FUNCTION__, $table);
}

/**
 * Get Ignores from DB Schema
 *
 * This function returns the ignores tables from the DB Schema file
 */
function get_ignores_from_dbschema()
{
    return __dbschema_helper(__FUNCTION__, "");
}

/**
 * Get Fulltext from DB Schema
 *
 * This function returns the fulltext tables from the DB Schema file
 */
function get_fulltext_from_dbschema()
{
    return __dbschema_helper(__FUNCTION__, "");
}

/**
 * Get Fkeys from DB Schema
 *
 * This function returns the fkeys from the DB Schema file
 *
 * @table => the table that you want to request the fkeys
 */
function get_fkeys_from_dbschema($table)
{
    return __dbschema_helper(__FUNCTION__, $table);
}

/**
 * DB Schema helper
 *
 * This function is a helper for the previous functions, is intended to be used
 * to returns the tables of the DB Schema or the fields of a table
 *
 * @fn => the caller function name
 * @table => the table used by some features
 */
function __dbschema_helper($fn, $table)
{
    static $tables = null;
    static $indexes = null;
    static $ignores = null;
    static $fulltext = null;
    static $fkeys = null;
    if ($tables === null) {
        $dbschema = eval_attr(xmlfile2array("xml/dbschema.xml"));
        $dbschema = __dbschema_auto_apps($dbschema);
        $dbschema = __dbschema_auto_fkey($dbschema);
        $dbschema = __dbschema_auto_name($dbschema);
        $tables = array();
        $indexes = array();
        $ignores = array();
        $fulltext = array();
        $fkeys = array();
        if (is_array($dbschema) && isset($dbschema["tables"]) && is_array($dbschema["tables"])) {
            foreach ($dbschema["tables"] as $tablespec) {
                if (isset($tablespec["#attr"]["ignore"]) && eval_bool($tablespec["#attr"]["ignore"])) {
                    $ignores[$tablespec["#attr"]["name"]] = 1;
                } else {
                    $tables[$tablespec["#attr"]["name"]] = array();
                    foreach ($tablespec["value"]["fields"] as $fieldspec) {
                        $tables[$tablespec["#attr"]["name"]][] = array(
                            "name" => $fieldspec["#attr"]["name"],
                            "type" => strtoupper(parse_query($fieldspec["#attr"]["type"]))
                        );
                        if (isset($fieldspec["#attr"]["fkey"]) && $fieldspec["#attr"]["fkey"] != "") {
                            if (!isset($fieldspec["#attr"]["fckeck"]) || eval_bool($fieldspec["#attr"]["fckeck"])) {
                                $fkeys[$tablespec["#attr"]["name"]][$fieldspec["#attr"]["name"]] = $fieldspec["#attr"]["fkey"];
                            }
                        }
                    }
                    if (isset($tablespec["value"]["indexes"])) {
                        $indexes[$tablespec["#attr"]["name"]] = array();
                        foreach ($tablespec["value"]["indexes"] as $indexspec) {
                            $indexes[$tablespec["#attr"]["name"]][parse_query($indexspec["#attr"]["name"])] = explode(",", $indexspec["#attr"]["fields"]);
                            if (isset($indexspec["#attr"]["fulltext"]) && eval_bool($indexspec["#attr"]["fulltext"])) {
                                $fulltext[$tablespec["#attr"]["name"]] = 1;
                            }
                        }
                    }
                }
            }
        }
    }
    if (stripos($fn, "get_tables") !== false) {
        return array_keys($tables);
    } elseif (stripos($fn, "get_fields") !== false) {
        if (isset($tables[$table])) {
            return $tables[$table];
        }
        return array();
    } elseif (stripos($fn, "get_indexes") !== false) {
        if (isset($indexes[$table])) {
            return $indexes[$table];
        }
        return array();
    } elseif (stripos($fn, "get_ignores") !== false) {
        return array_keys($ignores);
    } elseif (stripos($fn, "get_fulltext") !== false) {
        return array_keys($fulltext);
    } elseif (stripos($fn, "get_fkeys") !== false) {
        if (isset($fkeys[$table])) {
            return $fkeys[$table];
        }
        return array();
    }
    show_php_error(array("phperror" => "Unknown fn '$fn' in " . __FUNCTION__));
}

/**
 * DB Schema Auto Apps
 *
 * This function is a helper to the dbschema functions, to create an indexing table for each app
 *
 * @dbschema => the dbschema array
 *
 * Notes:
 *
 * This feature creates a table and try to use Mroonga storage engine with one field, the main
 * idea of this tables is to store all contents of the register to do quick searchs using a
 * fulltext search engine
 */
function __dbschema_auto_apps($dbschema)
{
    if (is_array($dbschema) && isset($dbschema["tables"]) && is_array($dbschema["tables"])) {
        $apps = get_apps_from_dbstatic();
        foreach ($apps as $app) {
            if (eval_bool(get_field_from_dbstatic($app, "has_indexing"))) {
                $xml = '<table name="__TABLE__">
                            <fields>
                                <field name="id" type="/*MYSQL INT(11) *//*SQLITE INTEGER */" pkey="true"/>
                                <field name="search" type="MEDIUMTEXT"/>
                            </fields>
                            <indexes>
                                <index fulltext="true" fields="search"/>
                            </indexes>
                        </table>';
                $xml = str_replace("__TABLE__", "idx_$app", $xml);
                $array = xml2array($xml);
                set_array($dbschema["tables"], "table", $array["table"]);
            }
            if (eval_bool(get_field_from_dbstatic($app, "has_control"))) {
                $xml = '<table name="__TABLE__">
                            <fields>
                                <field name="id" type="/*MYSQL INT(11) *//*SQLITE INTEGER */" pkey="true"/>
                                <field name="user_id" type="INT(11)" fkey="tbl_users"/>
                                <field name="datetime" type="DATETIME"/>
                            </fields>
                            <indexes>
                                <index fields="user_id"/>
                                <index fields="id,user_id"/>
                            </indexes>
                        </table>';
                $xml = str_replace("__TABLE__", "ctl_$app", $xml);
                $array = xml2array($xml);
                set_array($dbschema["tables"], "table", $array["table"]);
            }
            if (eval_bool(get_field_from_dbstatic($app, "has_version"))) {
                $xml = '<table name="__TABLE__">
                            <fields>
                                <field name="id" type="/*MYSQL INT(11) *//*SQLITE INTEGER */" pkey="true"/>
                                <field name="user_id" type="INT(11)" fkey="tbl_users"/>
                                <field name="datetime" type="DATETIME"/>
                                <field name="reg_id" type="INT(11)"/>
                                <field name="ver_id" type="INT(11)"/>
                                <field name="data" type="MEDIUMTEXT"/>
                                <field name="hash" type="VARCHAR(255)"/>
                            </fields>
                            <indexes>
                                <index fields="user_id"/>
                                <index fields="reg_id"/>
                                <index fields="ver_id"/>
                                <index fields="reg_id,ver_id"/>
                            </indexes>
                        </table>';
                $xml = str_replace("__TABLE__", "ver_$app", $xml);
                $array = xml2array($xml);
                set_array($dbschema["tables"], "table", $array["table"]);
            }
        }
    }
    return $dbschema;
}

/**
 * DB Schema Auto Fkey
 *
 * This function is a helper to the dbschema functions, to create an index for each fkey
 *
 * @dbschema => the dbschema array
 *
 * Notes:
 *
 * By default, MariaDB creates an index for each foreign key, but SQLite not does is by default
 * and for this reason, SaltOS creates an index automatically, to improve the performance
 *
 * This function checks that the field not exists in the defined indexes to prevent error in duplicates
 * indexes
 */
function __dbschema_auto_fkey($dbschema)
{
    if (is_array($dbschema) && isset($dbschema["tables"]) && is_array($dbschema["tables"])) {
        foreach ($dbschema["tables"] as $tablekey => $tablespec) {
            if (isset($tablespec["#attr"]["ignore"]) && eval_bool($tablespec["#attr"]["ignore"])) {
                continue;
            }
            $indexes = array();
            if (isset($dbschema["tables"][$tablekey]["value"]["indexes"])) {
                foreach ($dbschema["tables"][$tablekey]["value"]["indexes"] as $index) {
                    $indexes[] = $index["#attr"]["fields"];
                }
            }
            foreach ($tablespec["value"]["fields"] as $fieldkey => $fieldspec) {
                if (isset($fieldspec["#attr"]["fkey"]) && $fieldspec["#attr"]["fkey"] != "") {
                    if (in_array($fieldspec["#attr"]["name"], $indexes)) {
                        continue;
                    }
                    if (!isset($dbschema["tables"][$tablekey]["value"]["indexes"])) {
                        $dbschema["tables"][$tablekey]["value"]["indexes"] = array();
                    }
                    $xml = '<index fields="__FIELDS__"/>';
                    $xml = str_replace("__FIELDS__", $fieldspec["#attr"]["name"], $xml);
                    $array = xml2array($xml);
                    set_array($dbschema["tables"][$tablekey]["value"]["indexes"], "index", $array["index"]);
                }
            }
        }
    }
    return $dbschema;
}

/**
 * DB Schema Auto Name
 *
 * This function is a helper to the dbschema functions, to auto name the indexes
 *
 * @dbschema => the dbschema array
 *
 * Notes:
 *
 * This function allow to specify indexes only specifying the fields that you want
 * to conform the index, but the engines as MariaDB and SQLite, requires that each
 * index have a unique name, and for this reason, we add this feature to automate
 * this part of the process
 *
 * You can see how the name of the index is different for MySQL and SQLite, this is
 * because in MySQL, the name can be repeated in different tables, but in SQLite,
 * the name must be unique in the database
 */
function __dbschema_auto_name($dbschema)
{
    if (is_array($dbschema) && isset($dbschema["tables"]) && is_array($dbschema["tables"])) {
        foreach ($dbschema["tables"] as $tablekey => $tablespec) {
            if (isset($tablespec["#attr"]["ignore"]) && eval_bool($tablespec["#attr"]["ignore"])) {
                continue;
            }
            if (isset($tablespec["value"]["indexes"])) {
                $indexes[$tablespec["#attr"]["name"]] = array();
                foreach ($tablespec["value"]["indexes"] as $indexkey => $indexspec) {
                    if (!isset($indexspec["#attr"]["name"])) {
                        $table = $tablespec["#attr"]["name"];
                        $fields = $indexspec["#attr"]["fields"];
                        $dbschema["tables"][$tablekey]["value"]["indexes"][$indexkey]["#attr"]["name"] =
                            "/*MYSQL " . substr(str_replace(",", "_", $fields), 0, 64) . " */" .
                            "/*SQLITE " . substr($table . "_" . str_replace(",", "_", $fields), 0, 64) . " */";
                    }
                }
            }
        }
    }
    return $dbschema;
}

/**
 * Get Apps From DBStatic
 *
 * This function returns the list of apps that have a table and field defined
 * in the dbstatic file
 */
function get_apps_from_dbstatic()
{
    return __dbstatic_helper(__FUNCTION__, "", "");
}

/**
 * Get Field From DBStatic
 *
 * This function return the field associated to the table in the dbstatic
 * file and associated to the apps table
 *
 * @table => the table of the dbstatic that want to convert to field
 */
function get_field_from_dbstatic($table, $field = "field")
{
    return __dbstatic_helper(__FUNCTION__, $table, $field);
}

/**
 * DB Static helper
 *
 * This function is intended to act as helper of the dbstatic ecosystem, this
 * function can return the apps that contain table and field definitions and
 * too, can return the field associated to a apps table, usefull for the
 * indexing feature
 *
 * @fn => the caller function name
 * @table => the table used by some features
 */
function __dbstatic_helper($fn, $table, $field)
{
    static $apps = null;
    static $tables = array();
    if ($apps === null) {
        $apps = array();
        $tables = array();
        $dbstatic = eval_attr(xmlfile2array("xml/dbstatic.xml"));
        if (is_array($dbstatic) && isset($dbstatic["tables"]) && is_array($dbstatic["tables"])) {
            foreach ($dbstatic["tables"] as $data) {
                if (!isset($data["#attr"]["name"])) {
                    continue;
                }
                $table = $data["#attr"]["name"];
                if ($table != "tbl_apps") {
                    continue;
                }
                $rows = $data["value"];
                foreach ($rows as $row) {
                    if (isset($row["#attr"]["_table"]) && $row["#attr"]["_table"] != "") {
                        $apps[$row["#attr"]["code"]] = $row["#attr"];
                        $tables[$row["#attr"]["_table"]] = $row["#attr"];
                    }
                }
            }
        }
    }
    if (stripos($fn, "get_apps") !== false) {
        return array_keys($apps);
    } elseif (stripos($fn, "get_field") !== false) {
        if (isset($apps[$table][$field])) {
            return $apps[$table][$field];
        }
        if (isset($tables[$table][$field])) {
            return $tables[$table][$field];
        }
        return "";
    }
    show_php_error(array("phperror" => "Unknown fn '$fn' in " . __FUNCTION__));
}
