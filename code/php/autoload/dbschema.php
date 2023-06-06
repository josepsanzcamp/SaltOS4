<?php

/*
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

/*
 * DB Schema
 *
 * This function try to maintain the database structure, to do it, this feature uses the dbschema.xml
 * file to store the database structure.
 */
function db_schema()
{
    if (!eval_bool(get_default("db/dbschema"))) {
        return;
    }
    $hash1 = get_config("xml/dbschema.xml");
    $hash2 = md5(serialize(xml2array("xml/dbschema.xml")));
    if ($hash1 == $hash2) {
        return;
    }
    if (!semaphore_acquire(array("db_schema","db_static"), get_default("semaphoretimeout", 100000))) {
        return;
    }
    $dbschema = eval_attr(xml2array("xml/dbschema.xml"));
    if (is_array($dbschema) && isset($dbschema["tables"]) && is_array($dbschema["tables"])) {
        $tables1 = get_tables();
        $tables2 = get_tables_from_dbschema();
        if (isset($dbschema["excludes"]) && is_array($dbschema["excludes"])) {
            foreach ($dbschema["excludes"] as $exclude) {
                foreach ($tables1 as $key => $val) {
                    if ($exclude["#attr"]["name"] == $val) {
                        unset($tables1[$key]);
                    }
                }
                foreach ($tables2 as $key => $val) {
                    if ($exclude["#attr"]["name"] == $val) {
                        unset($tables2[$key]);
                    }
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
            if (isset($dbschema["indexes"]) && is_array($dbschema["indexes"])) {
                // This part allow the auto name of the indexes
                foreach ($dbschema["indexes"] as $key => $val) {
                    if (isset($val["#attr"]["name"])) {
                        continue;
                    }
                    $temp = array($val["#attr"]["table"]);
                    foreach ($val["value"]["fields"] as $val2) {
                        $temp[] = $val2["#attr"]["name"];
                    }
                    $temp = substr(implode("_", $temp), 0, 64);
                    $dbschema["indexes"][$key]["#attr"]["name"] = $temp;
                }
                // Continue with the original code
                $indexes1 = get_indexes($table);
                $indexes2 = array();
                foreach ($dbschema["indexes"] as $indexspec) {
                    if ($indexspec["#attr"]["table"] == $table) {
                        $indexes2[$indexspec["#attr"]["name"]] = array();
                        foreach ($indexspec["value"]["fields"] as $fieldspec) {
                            $indexes2[$indexspec["#attr"]["name"]][] = $fieldspec["#attr"]["name"];
                        }
                    }
                }
                foreach ($indexes1 as $index => $fields) {
                    if (!array_key_exists($index, $indexes2)) {
                        db_query(sql_drop_index($index, $table));
                    }
                }
                foreach ($dbschema["indexes"] as $indexspec) {
                    if ($indexspec["#attr"]["table"] == $table) {
                        $index = $indexspec["#attr"]["name"];
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
    }
    set_config("xml/dbschema.xml", $hash2);
    semaphore_release(array("db_schema","db_static"));
}

/*
 * DB Static
 *
 * This function try to maintain the database contents, to do it, this feature uses the dbstatic.xml
 * file to store the database contents that must to be maintaned.
 */
function db_static()
{
    if (!eval_bool(get_default("db/dbstatic"))) {
        return;
    }
    $hash1 = get_config("xml/dbstatic.xml");
    $hash2 = md5(serialize(xml2array("xml/dbstatic.xml")));
    if ($hash1 == $hash2) {
        return;
    }
    if (!semaphore_acquire(array("db_schema","db_static"), get_default("semaphoretimeout", 100000))) {
        return;
    }
    $dbstatic = eval_attr(xml2array("xml/dbstatic.xml"));
    if (is_array($dbstatic)) {
        foreach ($dbstatic as $table => $rows) {
            $query = "DELETE FROM {$table}";
            db_query($query);
            foreach ($rows as $row) {
                $query = make_insert_query($table, $row);
                db_query($query);
            }
        }
    }
    set_config("xml/dbstatic.xml", $hash2);
    semaphore_release(array("db_schema","db_static"));
}

/*
 * Get Tables from DB Schema
 *
 * This function returns the tables from the DB Schema file
 */
function get_tables_from_dbschema()
{
    return __dbschema_helper(__FUNCTION__, "");
}

/*
 * Get Fields from DB Schema
 *
 * This function returns the fields from the DB Schema file
 *
 * @table => the table that you want to request the files
 */
function get_fields_from_dbschema($table)
{
    return __dbschema_helper(__FUNCTION__, $table);
}

/*
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
    if ($tables === null) {
        $dbschema = eval_attr(xml2array("xml/dbschema.xml"));
        $tables = array();
        if (is_array($dbschema) && isset($dbschema["tables"]) && is_array($dbschema["tables"])) {
            foreach ($dbschema["tables"] as $tablespec) {
                $tables[$tablespec["#attr"]["name"]] = array();
                foreach ($tablespec["value"]["fields"] as $fieldspec) {
                    $tables[$tablespec["#attr"]["name"]][] = array(
                        "name" => $fieldspec["#attr"]["name"],
                        "type" => strtoupper(parse_query($fieldspec["#attr"]["type"]))
                    );
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
    }
    return array();
}
