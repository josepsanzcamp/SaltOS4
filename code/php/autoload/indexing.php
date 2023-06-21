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
 * Make Indexing main function
 *
 * This function implements the make indexing feature of SaltOS, this consists
 * in a concatenation of fields and subqueries to retrieve all data related to
 * the tables involved in the desired application and the register reg_id
 *
 * @app => code of the application that you want to index
 * @reg_id => register of the app that you want to index
 *
 * Notes:
 *
 * This function returns an integer as response about the indexing action:
 *
 * 1 => insert executed, this is because the app register exists and the indexing register not exists
 * 2 => update executed, this is because the app register exists and the indexing register too exists
 * 3 => delete executed, this is because the app register not exists and the indexing register exists
 * -1 => app not found, this is because the app requested not have a table in the apps config
 * -2 => indexing table not found, this is because the has_indexing feature is disabled by dbstatic
 * -3 => data not found, this is because the app register not exists and the indexting register too not exists
 *
 * As you can see, negative values denotes an error and positive values denotes a successfully situation
 */
function make_indexing($app, $reg_id)
{
    // Check the passed parameters
    $table = app2table($app);
    if ($table == "") {
        return -1;
    }
    // Search if index exists
    $query = "SELECT id FROM idx_$app WHERE id='$reg_id'";
    if (!db_check($query)) {
        return -2;
    }
    $indexing_id = execute_query($query);
    // Search if exists data in the main table
    $query = "SELECT id FROM $table WHERE id='$reg_id'";
    $data_id = execute_query($query);
    if (!$data_id) {
        if (!$indexing_id) {
            return -3;
        } else {
            $query = "DELETE FROM idx_$app WHERE id='$reg_id'";
            db_query($query);
            return 3;
        }
    }
    // Continue the process after the checks
    $queries = array();
    // This part allow to get all data of the all fields from the main table
    $fields = __make_indexing_helper($table, $reg_id);
    foreach ($fields as $key => $val) {
        $fields[$key] = "IFNULL(($val),'')";
    }
    $fields = "CONCAT(" . implode(",' ',", $fields) . ")";
    $query = "SELECT $fields FROM $table WHERE id='$reg_id'";
    $queries[] = $query;
    // This part allow to get all data of the all fields from the subtables
    $subtables = app2subtables($app);
    if ($subtables != "") {
        foreach (explode(",", $subtables) as $subtable) {
            $subtable = strtok($subtable, "(");
            $field = strtok(")");
            $fields = __make_indexing_helper($subtable);
            foreach ($fields as $key => $val) {
                $fields[$key] = "IFNULL(($val),'')";
            }
            $fields = "GROUP_CONCAT(CONCAT(" . implode(",' ',", $fields) . "))";
            $query = "SELECT $fields FROM $subtable WHERE $field='$reg_id'";
            $queries[] = $query;
        }
    }
    // OBTENER DATOS DE LAS TABLAS GENERICAS
    //~ $tables = array("tbl_ficheros","tbl_comentarios");
    //~ foreach ($tablas as $tabla) {
        //~ $campos = __make_indexing_helper($tabla);
        //~ foreach ($campos as $key => $val) {
            //~ $campos[$key] = "IFNULL(($val),'')";
        //~ }
        //~ $campos = "GROUP_CONCAT(CONCAT(" . implode(",' ',", $campos) . "))";
        //~ $query = "SELECT $campos
            //~ FROM $tabla
            //~ WHERE id_aplicacion='$id_aplicacion'
                //~ AND id_registro='$id_registro'";
        //~ $queries[] = $query;
    //~ }
    // Prepare the main query
    foreach ($queries as $key => $val) {
        $queries[$key] = "IFNULL(($val),'')";
    }
    $search = "CONCAT(" . implode(",' ',", $queries) . ")";
    // Do the insert or update action to the indexing table
    if (!$indexing_id) {
        $query = "INSERT INTO idx_$app(id,search) VALUES($reg_id,$search)";
        db_query($query);
        return 1;
    } else {
        $query = "UPDATE idx_$app SET search=$search WHERE id=$reg_id";
        db_query($query);
        return 2;
    }
}

/**
 * Make Indexing helper
 *
 * This function allow the make_indexing to retrieve all data of the fiels
 * and all data of the related fields of the related tables, this is done
 * by using the fkey information of the dbschema, this function uses some
 * features of the dbschema functions to get the fields, types, fkeys and
 * too, the dbstatic information of the app table
 *
 * This function uses a cache technique to improve the performance, returns
 * an array with all fields and subqueries to allow to retrieve all data
 * related to the app register
 */
function __make_indexing_helper($table, $id = "")
{
    static $cache = array();
    $hash = md5(serialize(array($table,$id)));
    if (isset($cache[$hash])) {
        return $cache[$hash];
    }
    $fieldnames = array_column(get_fields_from_dbschema($table), "name");
    if (!count($fieldnames)) {
        $fieldnames = array_column(get_fields($table), "name");
    }
    $result = $fieldnames;
    $tablefield = get_field_from_dbstatic($table);
    if ($tablefield != "") {
        $result[] = $tablefield;
    }
    $fieldfkeys = get_fkeys_from_dbschema($table);
    foreach ($fieldfkeys as $key => $val) {
        $temp = get_field_from_dbstatic($val);
        if ($temp == "") {
            $temp = implode(",' ',", array_column(get_fields_from_dbschema($val), "name"));
            if ($temp != "") {
                $temp = "CONCAT($temp)";
            }
        }
        if ($temp == "") {
            $temp = implode(",' ',", array_column(get_fields($val), "name"));
            if ($temp != "") {
                $temp = "CONCAT($temp)";
            }
        }
        $field = $temp;
        $type = get_field_type(array_column(get_fields_from_dbschema($table), "type", "name")[$key]);
        if ($type == "int") {
            if ($id == "") {
                $where = "$val.id=$key";
            } else {
                $where = "$val.id=(SELECT $key FROM $table WHERE id=$id)";
            }
        } elseif ($type == "string") {
            if ($id == "") {
                $where = "FIND_IN_SET($val.id,$key)";
            } else {
                $where = "FIND_IN_SET($val.id,(SELECT $key FROM $table WHERE id=$id))";
            }
            $field = "GROUP_CONCAT($field)";
        } else {
            $where = "";
        }
        if ($field != "" && $where != "") {
            $result[] = "(SELECT $field FROM $val WHERE $where)";
        }
    }
    $cache[$hash] = $result;
    return $result;
}
