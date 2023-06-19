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
 * About this file
 *
 * TODO
 */


//~ db_query("TRUNCATE TABLE idx_correo");

//~ $ids = execute_query_array("SELECT id FROM app_correo ORDER BY id ASC LIMIT 2000");
//~ foreach($ids as $id) {
    //~ echo "<pre>" . sprintr(make_indexing("correo", $id)) . "</pre>";
    //~ echo "<pre>" . sprintr(make_control("correo", $id)) . "</pre>";
//~ }

//~ set_config("xml/dbschema.xml", "nada");
//~ set_config("xml/dbstatic.xml", "nada");
//~ db_schema();
//~ db_static();

//~ set_config("xml/dbschema.xml", "nada");
//~ db_schema();
//~ db_query(sql_drop_index("user_id","ver_clientes"));
//~ set_config("xml/dbschema.xml", "nada");
//~ db_schema();

//~ echo "<pre>".sprintr(array_diff_assoc(
//~ array(
//~ "uno" => 1,
//~ "dos" => 2,
//~ "tres" => 3,
//~ ),
//~ array(
//~ "uno" => 1,
//~ "dos" => 3,
//~ "tres" => 3,
//~ )
//~ ))."</pre>";
//~ die();

//~ echo "<pre>" . sprintr(get_version("facturas", 1, 0)) . "</pre>";
//~ echo "<pre>" . sprintr(get_version("facturas", 1, 1)) . "</pre>";
//~ echo "<pre>" . sprintr(get_version("facturas", 1, 2)) . "</pre>";
//~ echo "<pre>" . sprintr(get_version("facturas", 1, 3)) . "</pre>";

db_query("DELETE FROM app_facturas WHERE id=1");
db_query("DELETE FROM app_facturas_c WHERE id_factura=1");
db_query("DELETE FROM app_facturas_v WHERE id_factura=1");
db_query("TRUNCATE TABLE ver_facturas");
//~ make_version("facturas",1);

/*********************************** INICIO PRIMERA VERSION *************************************/

$array = array(
    "id" => 1,
    "nombre" => "Josep Sanz",
    "num" => "",
);
$query = make_insert_query("app_facturas", $array);
db_query($query);

$array = array(
    "id" => 1,
    "id_factura" => 1,
    "concepto" => "Reloj",
    "unidades" => "1",
    "precio" => "99.99",
);
$query = make_insert_query("app_facturas_c", $array);
db_query($query);

$array = array(
    "id" => 2,
    "id_factura" => 1,
    "concepto" => "Correa",
    "unidades" => "1",
    "precio" => "19.99",
);
$query = make_insert_query("app_facturas_c", $array);
db_query($query);

add_version("facturas", 1);

/*********************************** INICIO SEGUNDA VERSION *************************************/

$array = array(
    "nombre" => "Josep Sanz Campderrós",
    "num" => "",
);

$query = make_update_query("app_facturas", $array, "id=1");
db_query($query);


$array = array(
    "id_factura" => 1,
    "concepto" => "Correa",
    "unidades" => "1",
    "precio" => "29.99",
);
$query = make_update_query("app_facturas_c", $array, "id=2");
db_query($query);

$array = array(
    "id" => 3,
    "id_factura" => 1,
    "concepto" => "Extras",
    "unidades" => "1",
    "precio" => "9.99",
);
$query = make_insert_query("app_facturas_c", $array);
db_query($query);

add_version("facturas", 1);

/*********************************** INICIO TERCERA VERSION *************************************/

$array = array(
    "nombre" => "Josep Sanz Campderrós",
    "num" => "123456789",
);

$query = make_update_query("app_facturas", $array, "id=1");
db_query($query);

$query = "DELETE FROM app_facturas_c WHERE id=1";
db_query($query);

add_version("facturas", 1);

/*********************************** INICIO DUMP VERSIONES *************************************/

echo "<pre>" . sprintr(get_version("facturas", 1, 0)) . "</pre>";
echo "<pre>" . sprintr(get_version("facturas", 1, 1)) . "</pre>";
echo "<pre>" . sprintr(get_version("facturas", 1, 2)) . "</pre>";
echo "<pre>" . sprintr(get_version("facturas", 1, 3)) . "</pre>";

die();
