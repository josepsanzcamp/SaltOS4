<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2023 by Josep Sanz Campderrós
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
 * TODO
 */

make_index("customers", 51);
make_control("customers", 51);

/*********************************** INICIO PRUEBAS customers *************************************/

db_query("DELETE FROM app_customers WHERE id=51");
db_query("TRUNCATE TABLE app_customers_version");
//~ make_version("customers",51);

$array = [
    "id" => 51,
    "nombre" => "Josep Sanz",
    "nombre1" => "Josep",
    "nombre2" => "Sanz",
    "nombre_poblacion" => "Barcelona",
    "nombre_codpostal" => "08030",
];
$query = make_insert_query("app_customers", $array);
db_query($query);

add_version("customers", 51);

$array = [
    "nombre" => "Josep Sanz Campderrós",
    "nombre2" => "Sanz Campderrós",
];

$query = make_update_query("app_customers", $array, "id=51");
db_query($query);

add_version("customers", 51);

$array = [
    "cif" => "123456789",
];

$query = make_update_query("app_customers", $array, "id=51");
db_query($query);

add_version("customers", 51);

echo "<pre>" . sprintr(get_version("customers", 51, 0)) . "</pre>";
echo "<pre>" . sprintr(get_version("customers", 51, 1)) . "</pre>";
echo "<pre>" . sprintr(get_version("customers", 51, 2)) . "</pre>";
echo "<pre>" . sprintr(get_version("customers", 51, 3)) . "</pre>";

/*********************************** INICIO PRUEBAS FACTURAS *************************************/

//~ echo "<pre>" . sprintr(get_version("facturas", 1, 0)) . "</pre>";
//~ echo "<pre>" . sprintr(get_version("facturas", 1, 1)) . "</pre>";
//~ echo "<pre>" . sprintr(get_version("facturas", 1, 2)) . "</pre>";
//~ echo "<pre>" . sprintr(get_version("facturas", 1, 3)) . "</pre>";

db_query("DELETE FROM app_facturas WHERE id=1");
db_query("DELETE FROM app_facturas_conceptos WHERE id_factura=1");
db_query("DELETE FROM app_facturas_vencimientos WHERE id_factura=1");
db_query("TRUNCATE TABLE app_facturas_version");
//~ make_version("facturas",1);

/*********************************** INICIO PRIMERA VERSION *************************************/

$array = [
    "id" => 1,
    "nombre" => "Josep Sanz",
    "num" => "",
];
$query = make_insert_query("app_facturas", $array);
db_query($query);

$array = [
    "id" => 1,
    "id_factura" => 1,
    "concepto" => "Reloj",
    "unidades" => "1",
    "precio" => "99.99",
];
$query = make_insert_query("app_facturas_conceptos", $array);
db_query($query);

$array = [
    "id" => 2,
    "id_factura" => 1,
    "concepto" => "Correa",
    "unidades" => "1",
    "precio" => "19.99",
];
$query = make_insert_query("app_facturas_conceptos", $array);
db_query($query);

add_version("facturas", 1);

/*********************************** INICIO SEGUNDA VERSION *************************************/

$array = [
    "nombre" => "Josep Sanz Campderrós",
    "num" => "",
];

$query = make_update_query("app_facturas", $array, "id=1");
db_query($query);


$array = [
    "id_factura" => 1,
    "concepto" => "Correa",
    "unidades" => "1",
    "precio" => "29.99",
];
$query = make_update_query("app_facturas_conceptos", $array, "id=2");
db_query($query);

$array = [
    "id" => 3,
    "id_factura" => 1,
    "concepto" => "Extras",
    "unidades" => "1",
    "precio" => "9.99",
];
$query = make_insert_query("app_facturas_conceptos", $array);
db_query($query);

add_version("facturas", 1);

/*********************************** INICIO TERCERA VERSION *************************************/

$array = [
    "nombre" => "Josep Sanz Campderrós",
    "num" => "123456789",
];

$query = make_update_query("app_facturas", $array, "id=1");
db_query($query);

$query = "DELETE FROM app_facturas_conceptos WHERE id=1";
db_query($query);

add_version("facturas", 1);

/*********************************** INICIO DUMP VERSIONES *************************************/

echo "<pre>" . sprintr(get_version("facturas", 1, 0)) . "</pre>";
echo "<pre>" . sprintr(get_version("facturas", 1, 1)) . "</pre>";
echo "<pre>" . sprintr(get_version("facturas", 1, 2)) . "</pre>";
echo "<pre>" . sprintr(get_version("facturas", 1, 3)) . "</pre>";

die();
