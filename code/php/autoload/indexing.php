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

// phpcs:disable Generic.Files.LineLength

/*
 * TODO
 */
function make_indexing($app, $reg_id = null)
{
    // CHECK PARAMETERS
    $app_id = app2id($app);
    $app_table = app2table($app);
    if ($app_table == "") {
        return -1;
    }
    $subtables = app2subtables($app);
    if ($reg_id === null) {
        $reg_id = execute_query("SELECT MAX(id) FROM $table");
    }
    if (is_string($reg_id) && strpos($reg_id, ",") !== false) {
        $reg_id = explode(",", $reg_id);
    }
    if (is_array($reg_id)) {
        $result = array();
        foreach ($reg_id as $id) {
            $result[] = make_indexing($app, $id);
        }
        return $result;
    }
    // BUSCAR SI EXISTE INDEXACION
    $query = "SELECT id FROM idx_$app WHERE id='$reg_id'";
    $indexing_id = execute_query($query);
    // BUSCAR SI EXISTEN DATOS DE LA TABLA PRINCIPAL
    $query = "SELECT id FROM $tabla WHERE id='$reg_id'";
    $data_id = execute_query($query);
    if (!$data_id) {
        if ($indexing_id) {
            $query = "DELETE FROM idx_$app WHERE id='$indexing_id'";
            db_query($query);
            return 3;
        } else {
            return -2;
        }
    }
    // CONTINUE
    $queries = array();
    // OBTENER DATOS DE LA TABLA PRINCIPAL
    $fields = __make_indexing_helper($table, $reg_id);
    foreach ($fields as $key => $val) {
        $fields[$key] = "IFNULL(($val),'')";
    }
    $fields = "CONCAT(" . implode(",' ',", $fields) . ")";
    $query = "SELECT $fields FROM $table WHERE id='$reg_id'";
    $queries[] = $query;
    // OBTENER DATOS DE LAS SUBTABLAS
    if ($subtables != "") {
        foreach (explode(",", $subtables) as $subtable) {
            $table = strtok($subtable, "(");
            $field = strtok(")");
            $fields = __make_indexing_helper($table);
            foreach ($fields as $key => $val) {
                $fields[$key] = "IFNULL(($val),'')";
            }
            $fields = "GROUP_CONCAT(CONCAT(" . implode(",' ',", $fields) . "))";
            $query = "SELECT {$fields} FROM {$table} WHERE {$field}='{$reg_id}'";
            $queries[] = $query;
        }
    }
    // OBTENER DATOS DE LAS TABLAS GENERICAS
    //~ $tables = array("tbl_ficheros","tbl_comentarios");
    //~ foreach ($tablas as $tabla) {
        //~ $campos = __make_indexing_helper($tabla);
        //~ foreach ($campos as $key => $val) {
            //~ $campos[$key] = "IFNULL(({$val}),'')";
        //~ }
        //~ $campos = "GROUP_CONCAT(CONCAT(" . implode(",' ',", $campos) . "))";
        //~ $query = "SELECT {$campos}
            //~ FROM {$tabla}
            //~ WHERE id_aplicacion='{$id_aplicacion}'
                //~ AND id_registro='{$id_registro}'";
        //~ $queries[] = $query;
    //~ }
    // PREPARAR QUERY PRINCIPAL
    foreach ($queries as $key => $val) {
        $queries[$key] = "IFNULL(($val),'')";
    }
    $search = "CONCAT(" . implode(",' ',", $queries) . ")";
    // AÑADIR A LA TABLA INDEXING
    if ($indexing_id) {
        $query = "UPDATE idx_$app SET search=$search WHERE id=$indexing_id";
        db_query($query);
        return 2;
    } else {
        $query = "REPLACE INTO idx_$app(id,search) VALUES($reg_id,$search)";
        db_query($query);
        return 1;
    }
}

/*
 * TODO
 */
function __make_indexing_helper($table, $id = "")
{
    static $cache = array();
    $hash = md5(serialize(array($table,$id)));
    if (isset($cache[$hash])) {
        return $cache[$hash];
    }
    static $tables = null;
    static $types = null;
    static $fields = null;
    static $campos = null;
    if ($tables === null) {
        $dbschema = eval_attr(xml_join(xml2array(detect_apps_files("xml/dbschema.xml"))));
        $tables = array();
        $types = array();
        $fields = array();
        if (is_array($dbschema) && isset($dbschema["tables"]) && is_array($dbschema["tables"])) {
            foreach ($dbschema["tables"] as $tablespec) {
                $tables[$tablespec["name"]] = array();
                $types[$tablespec["name"]] = array();
                $fields[$tablespec["name"]] = array();
                foreach ($tablespec["fields"] as $fieldspec) {
                    if (!isset($fieldspec["fkey"])) {
                        $fieldspec["fkey"] = "";
                    }
                    if (!isset($fieldspec["fcheck"])) {
                        $fieldspec["fcheck"] = "true";
                    }
                    if ($fieldspec["fkey"] != "" && eval_bool($fieldspec["fcheck"])) {
                        $tables[$tablespec["name"]][$fieldspec["name"]] = $fieldspec["fkey"];
                        $types[$tablespec["name"]][$fieldspec["name"]] = get_field_type($fieldspec["type"]);
                    }
                    $fields[$tablespec["name"]][] = $fieldspec["name"];
                }
            }
        }
    }
    if ($campos === null) {
        $dbstatic = eval_attr(xml_join(xml2array(detect_apps_files("xml/dbstatic.xml"))));
        $campos = array();
        if (is_array($dbstatic) && isset($dbstatic["tbl_aplicaciones"]) && is_array($dbstatic["tbl_aplicaciones"])) {
            foreach ($dbstatic["tbl_aplicaciones"] as $row) {
                if (isset($row["tabla"]) && isset($row["campo"])) {
                    if (substr($row["campo"], 0, 1) == '"' && substr($row["campo"], -1, 1) == '"') {
                        $row["campo"] = eval_protected($row["campo"]);
                    }
                    $campos[$row["tabla"]] = $row["campo"];
                }
            }
        }
    }
    if (!isset($fields[$tabla])) {
        $fields[$tabla] = array();
        foreach (get_fields($tabla) as $field) {
            $fields[$tabla][] = $field["name"];
        }
    }
    $result = $fields[$tabla];
    $result[] = "LPAD(id," . intval(get_config("zero_padding_digits")) . ",0)";
    if (isset($campos[$tabla])) {
        $result[] = $campos[$tabla];
    }
    if (isset($tables[$tabla])) {
        foreach ($tables[$tabla] as $key => $val) {
            if (isset($campos[$val])) {
                $campo = $campos[$val];
            } elseif (isset($fields[$val])) {
                $campo = "CONCAT(" . implode(",' ',", $fields[$val]) . ")";
            } else {
                $campo = "";
            }
            $type = $types[$tabla][$key];
            if ($type == "int") {
                if ($id == "") {
                    $where = "{$val}.id={$key}";
                } else {
                    $where = "{$val}.id=(SELECT {$key} FROM {$tabla} WHERE id={$id})";
                }
            } elseif ($type == "string") {
                if ($id == "") {
                    $where = "FIND_IN_SET({$val}.id,{$key})";
                } else {
                    $where = "FIND_IN_SET({$val}.id,(SELECT {$key} FROM {$tabla} WHERE id={$id}))";
                }
                $campo = "GROUP_CONCAT({$campo})";
            } else {
                $where = "";
            }
            if ($campo != "" && $where != "") {
                $result[] = "(SELECT {$campo} FROM {$val} WHERE {$where})";
            }
        }
    }
    $cache[$hash] = $result;
    return $result;
}
