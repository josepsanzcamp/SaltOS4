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
 * Main execution module
 *
 * This file contains the old index.php file, this was moved here to simplify the index.php and to
 * allow some php checks found in the current index.php
 *
 * This code implements the main method to access to the SaltOS API using rest and json requests, to
 * use it, you can use the follow methods:
 *
 * 1) Rest using GET requests
 *
 * This kind of requests requires that you send a GET request with a querystring of the follow
 * form:
 *
 * @https://127.0.0.1/saltos/code4/?app/invoices/view/2
 *
 * And the system process it of the follow form:
 *
 * @rest/1 => invoices
 * @rest/2 => view
 * @rest/3 => 2
 *
 * And you can programm any action that uses these parameters to do the desired task
 *
 * 2) Json using POST requests
 *
 * This other kind of requests requires that you send a POST request with the appropiate header
 * for the content-type as application/json and a json in the body of the request, with this
 * call, saltos can map all contents of the json to the json/????? variables.
 *
 * As an extra bonus, this module defines some useful server variables used in a lot of
 * features of saltos, like the follow vars:
 *
 * @request_method => can be GET or POST
 * @content_type   => used to check the content type for the JSON requests
 * @token          => used to validate the HTTP_TOKEN send as authentication
 * @remote_addr    => used internally for security reasons
 * @user_agent     => used internally for security reasons
 *
 * As a brief resume, you can use the follow keys in get_data or set_data:
 *
 * @rest                  => to get an array with all rest data, for the above example they
 *                           must return some thing like this:
 *                           ["app", "invoices", "view", "2"]
 * @rest/1                => to get only the element that contains "invoices"
 * @rest/2                => to get only the element that contains "view"
 * @rest/3                => to get only the element that contains "2"
 * @json                  => to get an array with all json data, for the above example they
 *                           must return some thing like this: ["user"=>"xxx", "pass"=>"xxx"]
 * @json/user             => to get only the element that contains the user
 * @json/pass             => to get only the element that contains the pass
 * @server                => to get an array with all server data
 * @server/request_method => can be GET or POST
 * @server/content_type   => used to check the content type for the JSON requests
 * @server/token          => used to validate the HTTP_TOKEN send as authentication
 * @server/remote_addr    => used internally for security reasons
 * @server/user_agent     => used internally for security reasons
 */

// Some important items
pcov_start();
program_handlers();
init_timer();
init_random();
check_system();

// Normal operation
$_CONFIG = eval_attr(xmlfiles2array(detect_config_files("xml/config.xml")));
eval_iniset(get_config("iniset"));
eval_putenv(get_config("putenv"));
eval_extras(get_config("extras"));

gc_exec(); // TODO: This is necessary or can be delegate to crontab
db_connect(); // TODO: This is necessary or can be called when needed
db_schema(); // TODO: This is necessary or can be called when needed
db_static(); // TODO: This is necessary or can be called when needed

// Collect all input data
if (isset($argv) && defined("STDIN")) {
    // This allow to use SaltOS from the command line using the CLI SAPI
    stream_set_blocking(STDIN, false); // Important if stdin is not used
    $_DATA = [
        "rest" => array_diff(explode("/", implode("/", array_slice($argv, 1))), [""]),
        "json" => array_protected(json_decode(file_get_contents("php://stdin"), true)),
        "server" => [
            "request_method" => "GET",
            "content_type" => "",
            "token" => check_token_format(getenv("TOKEN")),
            "remote_addr" => getenv("USER"),
            "user_agent" => "PHP/" . phpversion(),
        ],
    ];
    if (count(get_data("json"))) {
        set_data("server/request_method", "POST");
        set_data("server/content_type", "application/json");
    }
} else {
    $_DATA = [
        "rest" => array_diff(explode("/", get_server("QUERY_STRING") ?? ""), [""]),
        "json" => array_protected(json_decode(file_get_contents("php://input"), true)),
        "server" => [
            "request_method" => strtoupper(get_server("REQUEST_METHOD") ?? ""),
            "content_type" => strtolower(get_server("CONTENT_TYPE") ?? ""),
            "token" => check_token_format(get_server("HTTP_TOKEN")),
            "remote_addr" => get_server("REMOTE_ADDR") ?? "",
            "user_agent" => get_server("HTTP_USER_AGENT") ?? "",
        ],
    ];
}

//~ echo sprintr($_DATA); die();
//~ echo sprintr($_SERVER); die();

//~ set_data("server/token", execute_query("SELECT token FROM tbl_users_tokens WHERE active=1"));
//~ set_data("server/remote_addr", execute_query("SELECT remote_addr FROM tbl_users_tokens WHERE active=1"));
//~ set_data("server/user_agent", execute_query("SELECT user_agent FROM tbl_users_tokens WHERE active=1"));

//~ addlog(sprintr($_DATA));
//~ addlog(sprintr($_SERVER));

// Check for the main requirement: rest/0
if (get_data("rest/0") == "") {
    show_json_error("Unknown request");
}

// Check for a valid request_method
if (!in_array(get_data("server/request_method"), ["GET", "POST"])) {
    show_json_error("Unknown request");
}

// Check for a bad GET request_method
if (get_data("server/request_method") == "GET") {
    if (get_data("server/content_type") != "" || count(get_data("json"))) {
        show_json_error("Unknown request");
    }
}

// Check for a bad POST request_method
if (get_data("server/request_method") == "POST") {
    if (get_data("server/content_type") != "application/json" || !count(get_data("json"))) {
        show_json_error("Unknown request");
    }
}

// Try to execute the rest/0 if exists
set_data("rest/0", encode_bad_chars(get_data("rest/0")));
$action = "php/action/" . get_data("rest/0") . ".php";
if (file_exists($action)) {
    require $action;
}

// Otherwise, we don't know what to do with this request
show_json_error("Unknown request");
