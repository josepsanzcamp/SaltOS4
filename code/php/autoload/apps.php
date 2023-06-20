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

/**
 * Add list actions helper
 *
 * This function returns the input rows adding to each row an array of actions, to do
 * this function uses the permissions to check that each register and each action is
 * allowed by the token
 *
 * @rows => the rows used to do the list
 * @actions => an array with the actions that want to be added in each row
 *
 * TODO: THIS FUNCTION IS UNDER DEVELOPMENT
 */
function add_list_actions($rows, $actions)
{
    foreach ($rows as $key => $row) {
        $row["actions"] = array();
        foreach ($actions as $action) {
            $action["url"] = "app/{$action["app"]}/{$action["action"]}/{$row["id"]}";
            $row["actions"][] = $action;
        }
        // TODO: INICIO CODIGO TEST DISABLED
        if ($row["id"] == 48) {
            $row["actions"][2]["url"] = "";
        }
        if ($row["id"] == 47) {
            $row["actions"][1]["url"] = "";
            $row["actions"][2]["url"] = "";
        }
        if ($row["id"] == 46) {
            $row["actions"][1]["url"] = "";
        }
        // TODO: FIN CODIGO TEST DISABLED
        $rows[$key] = $row;
    }
    return $rows;
}

function __aplicaciones($tipo, $dato, $default)
{
    static $diccionario = array();
    if (!count($diccionario)) {
        $query = "SELECT id,code,_table,subtables FROM tbl_apps";
        $result = db_query($query);
        $diccionario["id2app"] = array();
        $diccionario["app2id"] = array();
        $diccionario["id2table"] = array();
        $diccionario["app2table"] = array();
        $diccionario["table2id"] = array();
        $diccionario["table2app"] = array();
        $diccionario["id2subtables"] = array();
        $diccionario["app2subtables"] = array();
        $diccionario["table2subtables"] = array();
        while ($row = db_fetch_row($result)) {
            $diccionario["id2app"][$row["id"]] = $row["code"];
            $diccionario["app2id"][$row["code"]] = $row["id"];
            $diccionario["id2table"][$row["id"]] = $row["_table"];
            $diccionario["app2table"][$row["code"]] = $row["_table"];
            $diccionario["table2id"][$row["_table"]] = $row["id"];
            $diccionario["table2app"][$row["_table"]] = $row["code"];
            $diccionario["id2subtables"][$row["id"]] = $row["subtables"];
            $diccionario["app2subtables"][$row["code"]] = $row["subtables"];
            $diccionario["table2subtables"][$row["_table"]] = $row["subtables"];
        }
        db_free($result);
    }
    if (!isset($diccionario[$tipo])) {
        return $default;
    }
    if (!isset($diccionario[$tipo][$dato])) {
        return $default;
    }
    return $diccionario[$tipo][$dato];
}

function id2app($id, $default = "")
{
    return __aplicaciones(__FUNCTION__, $id, $default);
}

function app2id($app, $default = "")
{
    return __aplicaciones(__FUNCTION__, $app, $default);
}

function id2table($id, $default = "")
{
    return __aplicaciones(__FUNCTION__, $id, $default);
}

function app2table($app, $default = "")
{
    return __aplicaciones(__FUNCTION__, $app, $default);
}

function table2id($table, $default = "")
{
    return __aplicaciones(__FUNCTION__, $table, $default);
}

function table2app($table, $default = "")
{
    return __aplicaciones(__FUNCTION__, $table, $default);
}

function id2subtables($id, $default = "")
{
    return __aplicaciones(__FUNCTION__, $id, $default);
}

function app2subtables($app, $default = "")
{
    return __aplicaciones(__FUNCTION__, $app, $default);
}

function table2subtables($table, $default = "")
{
    return __aplicaciones(__FUNCTION__, $table, $default);
}
