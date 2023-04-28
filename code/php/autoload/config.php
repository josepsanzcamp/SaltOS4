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

function get_config($key)
{
    $row = array();
    $query = "SELECT valor FROM tbl_configuracion WHERE clave='{$key}'";
    if (db_check($query)) {
        $config = execute_query($query);
    } else {
        $config = null;
    }
    if ($config !== null) {
        $row = array($key => $config);
    } else {
        $row = get_default("configs");
    }
    if (!isset($row[$key])) {
        return null;
    }
    return $row[$key];
}

function set_config($key, $val)
{
    $query = "SELECT valor FROM tbl_configuracion WHERE clave='{$key}'";
    $config = execute_query($query);
    if ($config === null) {
        $query = make_insert_query("tbl_configuracion", array(
            "clave" => $key,
            "valor" => $val
        ));
        db_query($query);
    } else {
        $query = make_update_query("tbl_configuracion", array(
            "valor" => $val
        ), make_where_query(array(
            "clave" => $key
        )));
        db_query($query);
    }
}

function get_default($key, $default = "")
{
    global $_CONFIG;
    $key = explode("/", $key);
    $count = count($key);
    $config = $_CONFIG;
    // TODO: REVISAR ESTE FRAGMENTO DE CODIGO
    //~ if ($count == 1 && isset($config["default"][$key[0]])) {
        //~ $config = $config["default"][$key[0]];
        //~ $count = 0;
    //~ }
    while ($count) {
        $key2 = array_shift($key);
        if (!isset($config[$key2])) {
            return $default;
        }
        $config = $config[$key2];
        $count--;
    }
    if ($config === "") {
        return $default;
    }
    return $config;
}
