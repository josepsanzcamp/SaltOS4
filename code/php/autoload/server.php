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

function get_server($index, $default = "")
{
    return isset($_SERVER[$index]) ? $_SERVER[$index] : $default;
}

// TODO: REVISAR ESTA FUNCION
//~ function force_ssl()
//~ {
    //~ // SOME CHECKS
    //~ if (!eval_bool(get_default("server/forcessl"))) {
        //~ return;
    //~ }
    //~ $serverport = get_server("SERVER_PORT");
    //~ $porthttps = get_default("server/porthttps", 443);
    //~ if ($serverport == $porthttps) {
        //~ return;
    //~ }
    //~ // MAIN VARIABLES
    //~ $protocol = "https://";
    //~ $servername = get_default("server/hostname", get_server("SERVER_NAME"));
    //~ $addedport = "";
    //~ $scriptname = get_default("server/pathname", get_server("SCRIPT_NAME"));
    //~ $querystring = get_server("QUERY_STRING");
    //~ // SOME CHECKS
    //~ if (substr($scriptname, 0, 1) != "/") {
        //~ $scriptname = "/" . $scriptname;
    //~ }
    //~ if (basename($scriptname) == get_default("server/dirindex", "index.php")) {
        //~ $scriptname = dirname($scriptname);
        //~ if (substr($scriptname, -1, 1) != "/") {
            //~ $scriptname .= "/";
        //~ }
    //~ }
    //~ // SOME CHECKS
    //~ if ($querystring) {
        //~ $querystring = "?" . str_replace("+", "%20", $querystring);
    //~ }
    //~ if ($porthttps != 443) {
        //~ $addedport = ":{$porthttps}";
    //~ }
    //~ // CONTINUE
    //~ $url = $protocol . $servername . $addedport . $scriptname . $querystring;
    //~ javascript_location($url);
    //~ die();
//~ }

// TODO: REVISAR ESTA FUNCION
//~ function get_base()
//~ {
    //~ // MAIN VARIABLES
    //~ $protocol = "http://";
    //~ $servername = get_default("server/hostname", get_server("SERVER_NAME"));
    //~ $addedport = "";
    //~ $scriptname = get_default("server/pathname", get_server("SCRIPT_NAME"));
    //~ // SOME CHECKS
    //~ if (substr($scriptname, 0, 1) != "/") {
        //~ $scriptname = "/" . $scriptname;
    //~ }
    //~ if (basename($scriptname) == get_default("server/dirindex", "index.php")) {
        //~ $scriptname = dirname($scriptname);
        //~ if (substr($scriptname, -1, 1) != "/") {
            //~ $scriptname .= "/";
        //~ }
    //~ }
    //~ // SOME CHECKS
    //~ $serverport = get_server("SERVER_PORT");
    //~ $porthttp = get_default("server/porthttp", 80);
    //~ $porthttps = get_default("server/porthttps", 443);
    //~ if ($serverport == $porthttp) {
        //~ $protocol = "http://";
        //~ if ($porthttp != 80) {
            //~ $addedport = ":$serverport";
        //~ }
    //~ }
    //~ if ($serverport == $porthttps) {
        //~ $protocol = "https://";
        //~ if ($porthttps != 443) {
            //~ $addedport = ":$serverport";
        //~ }
    //~ }
    //~ // CONTINUE
    //~ $url = $protocol . $servername . $addedport . $scriptname;
    //~ return $url;
//~ }
