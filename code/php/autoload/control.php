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
 * TODO
 */
function make_control($app, $reg_id = null, $user_id = null, $datetime = null)
{
   // Check the passed parameters
    $app_id = app2id($app);
    if (!$app_id) {
        return -1;
    }
    $table = app2table($app);
    if ($table == "") {
        return -2;
    }
    if ($reg_id === null) {
        $reg_id = execute_query("SELECT MAX(id) FROM $table");
    }
    if ($user_id === null) {
        $user_id = current_user();
    }
    if ($datetime === null) {
        $datetime = current_datetime();
    }
    if (is_string($reg_id) && strpos($reg_id, ",") !== false) {
        $reg_id = explode(",", $reg_id);
    }
    if (is_array($reg_id)) {
        $result = array();
        foreach ($reg_id as $id) {
            $result[] = make_control($app, $id, $user_id, $datetime);
        }
        return $result;
    }
    // Search if index exists
    $query = "SELECT id FROM reg_$app WHERE reg_id='$reg_id'";
    $control_id = execute_query($query);
    // Search if exists data in the main table
    $query = "SELECT id FROM $table WHERE id='$reg_id'";
    $data_id = execute_query($query);
    if (!$data_id) {
        if ($control_id) {
            $query = "DELETE FROM reg_$app WHERE id='$reg_id'";
            db_query($query);
            return 3;
        } else {
            return -3;
        }
    }



    // Buscar si existen datos de la tabla principal
    $query = "SELECT id FROM {$tabla} WHERE id='{$id_registro}'";
    $id_data = execute_query($query);
    if (!$id_data) {
        if ($id_control) {
            $query = "DELETE FROM tbl_registros
                WHERE id_aplicacion='{$id_aplicacion}'
                    AND id_registro='{$id_registro}'";
            db_query($query);
            return 3;
        } else {
            return -2;
        }
    }
    if ($id_control) {
        $query = make_insert_query("tbl_registros", array(
            "id_aplicacion" => $id_aplicacion,
            "id_registro" => $id_registro,
            "id_usuario" => $id_usuario,
            "datetime" => $datetime,
            "first" => 0
        ));
        db_query($query);
        return 2;
    } else {
        $query = make_insert_query("tbl_registros", array(
            "id_aplicacion" => $id_aplicacion,
            "id_registro" => $id_registro,
            "id_usuario" => $id_usuario,
            "datetime" => $datetime,
            "first" => 1
        ));
        db_query($query);
        return 1;
    }
}
