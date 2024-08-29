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
 * Database schema helper module
 *
 * This fie contains useful functions related to database schema, allow to manage the entire database
 * schema, and too, allow to maintain contents of some tables using the dbstatic feature
 */

/**
 * DB Schema
 *
 * This function try to maintain the database structure, to do it, this feature uses the dbschema.xml
 * file to store the database structure.
 */
function db_schema()
{
    $dbschema = eval_attr(xmlfiles2array(detect_apps_files('xml/dbschema.xml')));
    $dbschema = __dbschema_auto_apps($dbschema);
    $dbschema = __dbschema_auto_fkey($dbschema);
    $dbschema = __dbschema_auto_name($dbschema);
    $output = [
        'history' => [],
        'count' => 0,
    ];
    if (is_array($dbschema) && isset($dbschema['tables']) && is_array($dbschema['tables'])) {
        $ignores = get_ignores_from_dbschema();
        $tables1 = array_diff(get_tables(), $ignores);
        $tables2 = array_diff(get_tables_from_dbschema(), $ignores);
        foreach ($tables1 as $table) {
            $isbackup = (substr($table, 0, 2) == '__' && substr($table, -2, 2) == '__');
            if (!$isbackup && !in_array($table, $tables2)) {
                $backup = "__{$table}__";
                db_query(__dbschema_alter_table($table, $backup));
                $output['history'][] = "Rename $table to $backup";
                $output['count']++;
            }
        }
        foreach ($dbschema['tables'] as $tablespec) {
            $table = $tablespec['#attr']['name'];
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
                    db_query(__dbschema_alter_table($table, $backup));
                    db_query(__dbschema_create_table($tablespec));
                    foreach (get_indexes($table) as $index => $fields) {
                        db_query(__dbschema_drop_index($index, $table));
                    }
                    db_query(__dbschema_insert_from_select($table, $backup));
                    db_query(__dbschema_drop_table($backup));
                    $output['history'][] = "Alter $table";
                    $output['count']++;
                }
            } elseif (in_array($backup, $tables1)) {
                $fields1 = get_fields($backup);
                $fields2 = get_fields_from_dbschema($table);
                $hash3 = md5(serialize($fields1));
                $hash4 = md5(serialize($fields2));
                if ($hash3 != $hash4) {
                    db_query(__dbschema_create_table($tablespec));
                    foreach (get_indexes($table) as $index => $fields) {
                        db_query(__dbschema_drop_index($index, $table));
                    }
                    db_query(__dbschema_insert_from_select($table, $backup));
                    db_query(__dbschema_drop_table($backup));
                    $output['history'][] = "Alter $table from $backup";
                    $output['count']++;
                } else {
                    db_query(__dbschema_alter_table($backup, $table));
                    $output['history'][] = "Rename $backup to $table";
                    $output['count']++;
                }
            } else {
                db_query(__dbschema_create_table($tablespec));
                foreach (get_indexes($table) as $index => $fields) {
                    db_query(__dbschema_drop_index($index, $table));
                }
                $output['history'][] = "Create $table";
                $output['count']++;
            }
            $indexes1 = get_indexes($table);
            $indexes2 = get_indexes_from_dbschema($table);
            foreach ($indexes1 as $index => $fields) {
                if (!array_key_exists($index, $indexes2)) {
                    db_query(__dbschema_drop_index($index, $table));
                    $output['history'][] = "Drop $index on $table";
                    $output['count']++;
                }
            }
            if (isset($tablespec['value']['indexes']) && is_array($tablespec['value']['indexes'])) {
                foreach ($tablespec['value']['indexes'] as $indexspec) {
                    $indexspec['#attr']['table'] = $table;
                    // This parse_query is important because the name of the index is different
                    // for MySQL and SQLite and must to be parsed
                    $index = parse_query($indexspec['#attr']['name']);
                    if (array_key_exists($index, $indexes1)) {
                        $fields1 = $indexes1[$index];
                        $fields2 = $indexes2[$index];
                        $hash3 = md5(serialize($fields1));
                        $hash4 = md5(serialize($fields2));
                        if ($hash3 != $hash4) {
                            db_query(__dbschema_drop_index($index, $table));
                            db_query(__dbschema_create_index($indexspec));
                            $output['history'][] = "Alter $index on $table";
                            $output['count']++;
                        }
                    } else {
                        db_query(__dbschema_create_index($indexspec));
                        $output['history'][] = "Create $index on $table";
                        $output['count']++;
                    }
                }
            }
        }
    }
    set_config('xml/dbschema.xml', __dbschema_hash(), 0);
    return $output;
}

/**
 * DB Schema hash
 *
 * This function returns the hash used by db_schema
 */
function __dbschema_hash()
{
    return md5(serialize([
        xmlfiles2array(detect_apps_files('xml/dbschema.xml')),
        xmlfiles2array(detect_apps_files('xml/dbstatic.xml')),
        xmlfiles2array(detect_apps_files('xml/manifest.xml')),
    ]));
}

/**
 * DB Schema check
 *
 * This function returns the comparison between the old hash and the new hash
 */
function __dbschema_check()
{
    $hash1 = get_config('xml/dbschema.xml', 0);
    $hash2 = __dbschema_hash();
    return $hash1 == $hash2;
}

/**
 * DB Static
 *
 * This function try to maintain the database contents, to do it, this feature
 * uses the dbstatic.xml file to store the database contents that must to be
 * maintaned.
 *
 * This version of the db_static allow you to use a comma separated values in
 * fields as "id", start by "id_" or end by "_id"
 */
function db_static()
{
    $dbstatic = eval_attr(arrays2array(
        xmlfiles2array(detect_apps_files('xml/dbstatic.xml')),
        __manifest2dbstatic(detect_apps_files('xml/manifest.xml')),
    ));
    $output = [
        'history' => [],
        'count' => 0,
    ];
    if (is_array($dbstatic) && isset($dbstatic['tables']) && is_array($dbstatic['tables'])) {
        $queries = [];
        foreach ($dbstatic['tables'] as $data) {
            $table = $data['#attr']['name'];
            if (isset($output['history'][$table])) {
                continue;
            }
            $count = execute_query("SELECT COUNT(*) FROM $table");
            $query = "/*MYSQL TRUNCATE TABLE $table *//*SQLITE DELETE FROM $table */";
            $queries[] = $query;
            $output['history'][$table] = [
                'from' => $count,
                'to' => 0,
            ];
        }
        foreach ($dbstatic['tables'] as $data) {
            $table = $data['#attr']['name'];
            $rows = $data['value'];
            foreach ($rows as $row) {
                $temp = __dbstatic_insert($table, $row['#attr']);
                $queries = array_merge($queries, $temp);
                $output['history'][$table]['to'] += count($temp);
            }
        }
        $queries = __dbstatic_optimize_queries($queries);
        foreach ($queries as $query) {
            db_query($query);
        }
    }
    __manifest_perms_check(detect_apps_files('xml/manifest.xml'));
    set_config('xml/dbstatic.xml', __dbstatic_hash(), 0);
    foreach ($output['history'] as $key => $val) {
        $from = $val['from'];
        $to = $val['to'];
        $output['history'][$key] = "from $from to $to";
        $output['count'] += abs($to - $from);
    }
    return $output;
}

/**
 * DB Static optimize queries
 *
 * This function tries to join in the same insert multiple values packages
 * to improve the insert performance.
 *
 * @queries => The array of queries to be optimised if it is possible
 */
function __dbstatic_optimize_queries($queries)
{
    $array = [];
    foreach ($queries as $index => $query) {
        if (substr($query, 0, 11) != 'INSERT INTO') {
            continue;
        }
        $pos = strpos($query, 'VALUES');
        if (!$pos) {
            continue;
        }
        $key = substr($query, 0, $pos + 6);
        $val = substr($query, $pos + 6);
        if (!isset($array[$key])) {
            $array[$key] = [];
        }
        $array[$key][] = $val;
        unset($queries[$index]);
    }
    foreach ($array as $key => $val) {
        $queries[] = $key . implode(',', $val);
    }
    return $queries;
}

/**
 * DB Static hash
 *
 * This function returns the hash used by db_static
 */
function __dbstatic_hash()
{
    return md5(serialize([
        xmlfiles2array(detect_apps_files('xml/dbstatic.xml')),
        xmlfiles2array(detect_apps_files('xml/manifest.xml')),
    ]));
}

/**
 * DB Static check
 *
 * This function returns the comparison between the old hash and the new hash
 */
function __dbstatic_check()
{
    $hash1 = get_config('xml/dbstatic.xml', 0);
    $hash2 = __dbstatic_hash();
    return $hash1 == $hash2;
}

/**
 * DB Static insert
 *
 * This function is a helper of previous function, is intended to be used by db_static and
 * allow to use a comma separated values in fields as "id", start by "id_" or end by "_id"
 *
 * @table => the table that you want to use in the insert process
 * @row   => the row that you want to add in the table
 *
 * Notes:
 *
 * This feature allow to you to use comma separated lists of values, commonly used for id
 * fields as user_id, perms_id, or similar.
 */
function __dbstatic_insert($table, $row)
{
    foreach ($row as $field => $value) {
        if ($field == 'id' || substr($field, 0, 3) == 'id_' || substr($field, -3, 3) == '_id') {
            if (strpos($value, ',') !== false) {
                $a = explode(',', $row[$field]);
                $queries = [];
                foreach ($a as $b) {
                    $row[$field] = $b;
                    $queries = array_merge($queries, __dbstatic_insert($table, $row));
                }
                return $queries;
            }
        }
    }
    // Original insert query
    $query = make_insert_query($table, $row);
    return [$query];
}

/**
 * Get Tables from DB Schema
 *
 * This function returns the tables from the DB Schema file
 */
function get_tables_from_dbschema()
{
    return __dbschema_helper(__FUNCTION__, '');
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
    return __dbschema_helper(__FUNCTION__, '');
}

/**
 * Get Fulltext from DB Schema
 *
 * This function returns the fulltext tables from the DB Schema file
 */
function get_fulltext_from_dbschema()
{
    return __dbschema_helper(__FUNCTION__, '');
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
 * @fn    => the caller function name
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
        $dbschema = eval_attr(xmlfiles2array(detect_apps_files('xml/dbschema.xml')));
        $dbschema = __dbschema_auto_apps($dbschema);
        $dbschema = __dbschema_auto_fkey($dbschema);
        $dbschema = __dbschema_auto_name($dbschema);
        $tables = [];
        $indexes = [];
        $ignores = [];
        $fulltext = [];
        $fkeys = [];
        if (is_array($dbschema) && isset($dbschema['tables']) && is_array($dbschema['tables'])) {
            foreach ($dbschema['tables'] as $tablespec) {
                if (isset($tablespec['#attr']['ignore']) && eval_bool($tablespec['#attr']['ignore'])) {
                    $ignores[$tablespec['#attr']['name']] = 1;
                    continue;
                }
                $tables[$tablespec['#attr']['name']] = [];
                foreach ($tablespec['value']['fields'] as $fieldspec) {
                    $tables[$tablespec['#attr']['name']][] = [
                        'name' => $fieldspec['#attr']['name'],
                        'type' => strtoupper(parse_query($fieldspec['#attr']['type'])),
                    ];
                    if (isset($fieldspec['#attr']['fkey']) && $fieldspec['#attr']['fkey'] != '') {
                        $fkeys[$tablespec['#attr']['name']][$fieldspec['#attr']['name']]
                            = $fieldspec['#attr']['fkey'];
                    }
                }
                if (isset($tablespec['value']['indexes'])) {
                    $indexes[$tablespec['#attr']['name']] = [];
                    foreach ($tablespec['value']['indexes'] as $indexspec) {
                        $indexes[$tablespec['#attr']['name']][parse_query($indexspec['#attr']['name'])]
                            = explode(',', $indexspec['#attr']['fields']);
                        if (
                            isset($indexspec['#attr']['fulltext']) &&
                            eval_bool($indexspec['#attr']['fulltext'])
                        ) {
                            $fulltext[$tablespec['#attr']['name']] = 1;
                        }
                    }
                }
            }
        }
    }
    if (stripos($fn, 'get_tables') !== false) {
        return array_keys($tables);
    } elseif (stripos($fn, 'get_fields') !== false) {
        if (isset($tables[$table])) {
            return $tables[$table];
        }
        return [];
    } elseif (stripos($fn, 'get_indexes') !== false) {
        if (isset($indexes[$table])) {
            return $indexes[$table];
        }
        return [];
    } elseif (stripos($fn, 'get_ignores') !== false) {
        return array_keys($ignores);
    } elseif (stripos($fn, 'get_fulltext') !== false) {
        return array_keys($fulltext);
    } elseif (stripos($fn, 'get_fkeys') !== false) {
        if (isset($fkeys[$table])) {
            return $fkeys[$table];
        }
        return [];
    }
    show_php_error(['phperror' => "Unknown fn '$fn'"]);
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
    if (is_array($dbschema) && isset($dbschema['tables']) && is_array($dbschema['tables'])) {
        $tables = get_tables_from_dbstatic();
        foreach ($tables as $table) {
            if (eval_bool(get_field_from_dbstatic($table, 'has_index'))) {
                // phpcs:disable Generic.Files.LineLength
                $xml = '<table name="{$table}_index">
                            <fields>
                                <field name="id" type="/*MYSQL INT(11) *//*SQLITE INTEGER */" pkey="true"/>
                                <field name="search" type="MEDIUMTEXT"/>
                            </fields>
                            <indexes>
                                <index fulltext="true" fields="search"/>
                            </indexes>
                        </table>';
                // phpcs:enable Generic.Files.LineLength
                $xml = str_replace('{$table}', "{$table}", $xml);
                $array = xml2array($xml);
                set_array($dbschema['tables'], 'table', $array['table']);
            }
            if (eval_bool(get_field_from_dbstatic($table, 'has_control'))) {
                // phpcs:disable Generic.Files.LineLength
                $xml = '<table name="{$table}_control">
                            <fields>
                                <field name="id" type="/*MYSQL INT(11) *//*SQLITE INTEGER */" pkey="true" fkey="{$table}"/>
                                <field name="user_id" type="INT(11)" fkey="tbl_users"/>
                                <field name="group_id" type="INT(11)" fkey="tbl_groups"/>
                                <field name="datetime" type="DATETIME"/>
                                <field name="users_id" type="TEXT"/>
                                <field name="groups_id" type="TEXT"/>
                            </fields>
                            <indexes>
                                <index fields="user_id"/>
                                <index fields="id,user_id"/>
                            </indexes>
                        </table>';
                // phpcs:enable Generic.Files.LineLength
                $xml = str_replace('{$table}', "{$table}", $xml);
                $array = xml2array($xml);
                set_array($dbschema['tables'], 'table', $array['table']);
            }
            if (eval_bool(get_field_from_dbstatic($table, 'has_version'))) {
                $xml = '<table name="{$table}_version">
                            <fields>
                                <field name="id" type="/*MYSQL INT(11) *//*SQLITE INTEGER */" pkey="true"/>
                                <field name="user_id" type="INT(11)" fkey="tbl_users"/>
                                <field name="datetime" type="DATETIME"/>
                                <field name="reg_id" type="INT(11)" fkey="{$table}"/>
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
                $xml = str_replace('{$table}', "{$table}", $xml);
                $array = xml2array($xml);
                set_array($dbschema['tables'], 'table', $array['table']);
            }
            if (eval_bool(get_field_from_dbstatic($table, 'has_files'))) {
                $xml = '<table name="{$table}_files">
                            <fields>
                                <field name="id" type="/*MYSQL INT(11) *//*SQLITE INTEGER */" pkey="true"/>
                                <field name="user_id" type="INT(11)" fkey="tbl_users"/>
                                <field name="datetime" type="DATETIME"/>
                                <field name="reg_id" type="INT(11)" fkey="{$table}"/>
                                <field name="uniqid" type="VARCHAR(255)"/>
                                <field name="name" type="VARCHAR(255)"/>
                                <field name="size" type="INT(11)"/>
                                <field name="type" type="VARCHAR(255)"/>
                                <field name="file" type="VARCHAR(255)"/>
                                <field name="hash" type="VARCHAR(255)"/>
                                <field name="search" type="MEDIUMTEXT"/>
                                <field name="indexed" type="INT(11)"/>
                                <field name="retries" type="INT(11)"/>
                            </fields>
                        </table>';
                $xml = str_replace('{$table}', "{$table}", $xml);
                $array = xml2array($xml);
                set_array($dbschema['tables'], 'table', $array['table']);
            }
            if (eval_bool(get_field_from_dbstatic($table, 'has_notes'))) {
                $xml = '<table name="{$table}_notes">
                            <fields>
                                <field name="id" type="/*MYSQL INT(11) *//*SQLITE INTEGER */" pkey="true"/>
                                <field name="user_id" type="INT(11)" fkey="tbl_users"/>
                                <field name="datetime" type="DATETIME"/>
                                <field name="reg_id" type="INT(11)" fkey="{$table}"/>
                                <field name="note" type="TEXT"/>
                            </fields>
                        </table>';
                $xml = str_replace('{$table}', "{$table}", $xml);
                $array = xml2array($xml);
                set_array($dbschema['tables'], 'table', $array['table']);
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
    if (is_array($dbschema) && isset($dbschema['tables']) && is_array($dbschema['tables'])) {
        foreach ($dbschema['tables'] as $tablekey => $tablespec) {
            if (isset($tablespec['#attr']['ignore']) && eval_bool($tablespec['#attr']['ignore'])) {
                continue;
            }
            $indexes = [];
            if (isset($dbschema['tables'][$tablekey]['value']['indexes'])) {
                foreach ($dbschema['tables'][$tablekey]['value']['indexes'] as $index) {
                    $indexes[] = $index['#attr']['fields'];
                }
            }
            foreach ($tablespec['value']['fields'] as $fieldkey => $fieldspec) {
                if (isset($fieldspec['#attr']['fkey']) && $fieldspec['#attr']['fkey'] != '') {
                    if (in_array($fieldspec['#attr']['name'], $indexes)) {
                        continue;
                    }
                    if (!isset($dbschema['tables'][$tablekey]['value']['indexes'])) {
                        $dbschema['tables'][$tablekey]['value']['indexes'] = [];
                    }
                    $xml = '<index fields="__FIELDS__"/>';
                    $xml = str_replace('__FIELDS__', $fieldspec['#attr']['name'], $xml);
                    $array = xml2array($xml);
                    set_array($dbschema['tables'][$tablekey]['value']['indexes'], 'index', $array['index']);
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
    if (is_array($dbschema) && isset($dbschema['tables']) && is_array($dbschema['tables'])) {
        foreach ($dbschema['tables'] as $tablekey => $tablespec) {
            if (isset($tablespec['#attr']['ignore']) && eval_bool($tablespec['#attr']['ignore'])) {
                continue;
            }
            if (isset($tablespec['value']['indexes'])) {
                $indexes[$tablespec['#attr']['name']] = [];
                foreach ($tablespec['value']['indexes'] as $indexkey => $indexspec) {
                    if (!isset($indexspec['#attr']['name'])) {
                        $table = $tablespec['#attr']['name'];
                        $fields = $indexspec['#attr']['fields'];
                        $dbschema['tables'][$tablekey]['value']['indexes'][$indexkey]['#attr']['name'] =
                        '/*MYSQL ' . substr(str_replace(',', '_', $fields), 0, 64) . ' */' .
                        '/*SQLITE ' . substr($table . '_' . str_replace(',', '_', $fields), 0, 64) . ' */';
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
    return __dbstatic_helper(__FUNCTION__, '', '');
}

/**
 * Get Tables From DBStatic
 *
 * This function returns the list of tables that have a table and field defined
 * in the dbstatic file
 */
function get_tables_from_dbstatic()
{
    return __dbstatic_helper(__FUNCTION__, '', '');
}

/**
 * Get Field From DBStatic
 *
 * This function return the field associated to the table in the dbstatic
 * file and associated to the apps table
 *
 * @table => the table of the dbstatic that want to convert to field
 * @field => the field name, field by default
 *
 * Notes:
 *
 * This function uses the special feature in the helper that allow to
 * use as table parameter an app code to retrieve the field, this is
 * useful if you want some field of the app table and you want to use
 * the app code instead of the app table to identify what row do you
 * want to use
 */
function get_field_from_dbstatic($table, $field = 'field')
{
    return __dbstatic_helper(__FUNCTION__, $table, $field);
}

/**
 * DB Static helper
 *
 * This function is intended to act as helper of the dbstatic ecosystem, this
 * function can return the apps that contain table and field definitions and
 * too, can return the field associated to a apps table, useful for the
 * indexing feature
 *
 * @fn    => the caller function name
 * @table => the table used by some features
 * @field => the field used by some features
 */
function __dbstatic_helper($fn, $table, $field)
{
    static $apps = null;
    static $tables = [];
    if ($apps === null) {
        $apps = [];
        $tables = [];
        $dbstatic = eval_attr(arrays2array(
            xmlfiles2array(detect_apps_files('xml/dbstatic.xml')),
            __manifest2dbstatic(detect_apps_files('xml/manifest.xml')),
        ));
        if (is_array($dbstatic) && isset($dbstatic['tables']) && is_array($dbstatic['tables'])) {
            foreach ($dbstatic['tables'] as $data) {
                if ($data['#attr']['name'] != 'tbl_apps') {
                    continue;
                }
                $rows = $data['value'];
                foreach ($rows as $row) {
                    if (isset($row['#attr']['table']) && $row['#attr']['table'] != '') {
                        $apps[$row['#attr']['code']] = $row['#attr'];
                        $tables[$row['#attr']['table']] = $row['#attr'];
                    }
                }
            }
        }
    }
    if (stripos($fn, 'get_apps') !== false) {
        return array_keys($apps);
    } elseif (stripos($fn, 'get_tables') !== false) {
        return array_keys($tables);
    } elseif (stripos($fn, 'get_field') !== false) {
        if (isset($apps[$table][$field])) {
            return $apps[$table][$field];
        }
        if (isset($tables[$table][$field])) {
            return $tables[$table][$field];
        }
        return '';
    }
    show_php_error(['phperror' => "Unknown fn '$fn'"]);
}

/**
 * Manifest to dbstatic
 *
 * This function returns the equivalent dbstatic data using as input the contents
 * of the manifests files.
 *
 * @files => An array with all the manifests files
 */
function __manifest2dbstatic($files)
{
    $dbstatic = ['tables' => []];
    foreach ($files as $file) {
        $data = xmlfile2array($file);
        if (!is_array($data) || !isset($data['apps']) || !is_array($data['apps'])) {
            show_php_error(['phperror' => "File $file must contains a valid apps node"]);
        }
        foreach ($data['apps'] as $app) {
            if (!isset($app['perms']) || !is_array($app['perms'])) {
                show_php_error(['phperror' => "File $file must contains a valid perms node"]);
            }
            $perms = $app['perms'];
            unset($app['perms']);
            // Add the apps data package
            $xml = '<table name="tbl_apps">
                        <row id="" active="" code="" name="" description="" table="" subtables="" field=""
                            has_index="0" has_control="0" has_version="0" has_files="0" has_notes="0"/>
                    </table>';
            $array = xml2array($xml);
            foreach ($app as $key => $val) {
                $array['table']['value']['row']['#attr'][$key] = $val;
            }
            set_array($dbstatic['tables'], 'table', $array['table']);
            // Add the perms data package
            if (is_attr_value($perms)) {
                $value = $perms['value'];
                $attr = $perms['#attr'];
            } else {
                $value = $perms;
                $attr = [];
            }
            $perm_id = [];
            foreach ($value as $perm) {
                $perm0 = strtok($perm, ',');
                if (!is_numeric($perm0)) {
                    show_php_error(['phperror' => "Unknown perm '$perm'"]);
                }
                $perm_id[] = $perm0;
            }
            $perm_id = implode(',', $perm_id);
            $xml = '<table name="tbl_apps_perms">
                        <row app_id="" perm_id="" allow="0" deny="0"/>
                    </table>';
            $array = xml2array($xml);
            $array['table']['value']['row']['#attr']['app_id'] = $app['id'];
            $array['table']['value']['row']['#attr']['perm_id'] = $perm_id;
            if (isset($attr['allow'])) {
                $array['table']['value']['row']['#attr']['allow'] = $attr['allow'];
            }
            if (isset($attr['deny'])) {
                $array['table']['value']['row']['#attr']['deny'] = $attr['deny'];
            }
            set_array($dbstatic['tables'], 'table', $array['table']);
        }
    }
    return $dbstatic;
}

/**
 * Manifest perms check
 *
 * This function checks the integrity of the perms nodes in all manifest files.
 *
 * @files => An array with all the manifests files
 */
function __manifest_perms_check($files)
{
    foreach ($files as $file) {
        $data = xmlfile2array($file);
        foreach ($data['apps'] as $app) {
            $perms = $app['perms'];
            unset($app['perms']);
            if (is_attr_value($perms)) {
                $value = $perms['value'];
                $attr = $perms['#attr'];
            } else {
                $value = $perms;
                $attr = [];
            }
            foreach ($value as $perm) {
                $perm_array = explode(',', $perm . ',,');
                $exists = execute_query('SELECT id FROM tbl_perms WHERE ' . make_where_query([
                    'id' => $perm_array[0],
                    'code' => $perm_array[1],
                    'owner' => $perm_array[2],
                ]));
                if (!$exists) {
                    show_php_error(['phperror' => "Perm '$perm' not found"]);
                }
            }
        }
    }
}

/**
 * DB Schema Create Table
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
function __dbschema_create_table($tablespec)
{
    $table = $tablespec['#attr']['name'];
    $fields = [];
    foreach ($tablespec['value']['fields'] as $field) {
        $name = $field['#attr']['name'];
        $type = $field['#attr']['type'];
        $type2 = get_field_type($type);
        if ($type2 == 'int') {
            $def = intval(0);
        } elseif ($type2 == 'float') {
            $def = floatval(0);
        } elseif ($type2 == 'date') {
            $def = dateval(0);
        } elseif ($type2 == 'time') {
            $def = timeval(0);
        } elseif ($type2 == 'datetime') {
            $def = datetimeval(0);
        } elseif ($type2 == 'string') {
            $def = '';
        } else {
            // @codeCoverageIgnoreStart
            show_php_error(['phperror' => "Unknown type '$type'"]);
            // @codeCoverageIgnoreEnd
        }
        $extra = "NOT NULL DEFAULT '$def'";
        if (isset($field['#attr']['pkey']) && eval_bool($field['#attr']['pkey'])) {
            $extra = 'PRIMARY KEY /*MYSQL AUTO_INCREMENT *//*SQLITE AUTOINCREMENT */';
        }
        $name2 = escape_reserved_word($name);
        $fields[] = "$name2 $type $extra";
    }
    foreach ($tablespec['value']['fields'] as $field) {
        if (isset($field['#attr']['fkey'])) {
            $fkey = $field['#attr']['fkey'];
            if ($fkey != '') {
                $name = $field['#attr']['name'];
                $fields[] = "FOREIGN KEY ($name) REFERENCES $fkey (id)";
            }
        }
    }
    $fields = implode(',', $fields);
    $post = '/*MYSQL ENGINE=MyISAM CHARSET=utf8mb4 */';
    if (in_array($table, get_fulltext_from_dbschema()) && __has_engine('mroonga')) {
        $post = '/*MYSQL ENGINE=Mroonga CHARSET=utf8mb4 */';
    } elseif (__has_engine('aria')) {
        $post = '/*MYSQL ENGINE=Aria CHARSET=utf8mb4 */';
    }
    $query = "CREATE TABLE $table ($fields) $post";
    return $query;
}

/**
 * DB Schema Alter Table
 *
 * This function returns the alter table command
 *
 * @orig => source table
 * @dest => destination table
 */
function __dbschema_alter_table($orig, $dest)
{
    $query = "ALTER TABLE $orig RENAME TO $dest";
    return $query;
}

/**
 * DB Schema Insert From Select
 *
 * This function returns the insert from select command
 *
 * @orig => source table
 * @dest => destination table
 */
function __dbschema_insert_from_select($dest, $orig)
{
    $fdest = get_fields($dest);
    $ldest = [];
    foreach ($fdest as $f) {
        $ldest[] = $f['name'];
    }
    $forig = get_fields($orig);
    $lorig = [];
    foreach ($forig as $f) {
        $lorig[] = $f['name'];
    }
    $defs = [];
    foreach ($fdest as $f) {
        $type = $f['type'];
        $type2 = get_field_type($type);
        if ($type2 == 'int') {
            $defs[] = intval(0);
        } elseif ($type2 == 'float') {
            $defs[] = floatval(0);
        } elseif ($type2 == 'date') {
            $defs[] = dateval(0);
        } elseif ($type2 == 'time') {
            $defs[] = timeval(0);
        } elseif ($type2 == 'datetime') {
            $defs[] = datetimeval(0);
        } elseif ($type2 == 'string') {
            $defs[] = '';
        } else {
            // @codeCoverageIgnoreStart
            show_php_error(['phperror' => "Unknown type '$type'"]);
            // @codeCoverageIgnoreEnd
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
    $keys = implode(',', $keys);
    $vals = implode(',', $vals);
    $query = "INSERT INTO $dest($keys) SELECT $vals FROM $orig";
    return $query;
}

/**
 * DB Schema Drop Table
 *
 * This function returns the drop table command
 *
 * @table => table that you want to drop
 */
function __dbschema_drop_table($table)
{
    $query = "DROP TABLE $table";
    return $query;
}

/**
 * DB Schema Create Index
 *
 * This function returns the SQL needed to create the index defined in the
 * indexspec argument
 *
 * @indexspec => the specification for the create index, see the dbschema
 *               file to understand the indexspec structure
 *
 * This function creates the index, supports fulltext indexes
 */
function __dbschema_create_index($indexspec)
{
    $name = $indexspec['#attr']['name'];
    $table = $indexspec['#attr']['table'];
    $fields = $indexspec['#attr']['fields'];
    $fields = explode(',', $fields);
    foreach ($fields as $key => $val) {
        $fields[$key] = escape_reserved_word($val);
    }
    $fields = implode(',', $fields);
    $pre = '';
    if (isset($indexspec['#attr']['fulltext']) && eval_bool($indexspec['#attr']['fulltext'])) {
        $pre = '/*MYSQL FULLTEXT */';
    }
    $query = "CREATE $pre INDEX $name ON $table ($fields)";
    return $query;
}

/**
 * DB Schema Drop Index
 *
 * This function returns the drop index command
 *
 * @index => index that you want to drop
 * @table => table where the indes is part of
 */
function __dbschema_drop_index($index, $table)
{
    $query = "/*MYSQL DROP INDEX $index ON $table *//*SQLITE DROP INDEX $index */";
    return $query;
}
