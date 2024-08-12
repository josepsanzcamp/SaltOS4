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
 * Apps helper module
 *
 * This file contains functions intended to be used as hepers of other functions, allowing to convert
 * between formats as the name of the app to and app id, or viceversa
 */

/**
 * Apps helper function
 *
 * This function is used by the XXX2YYY functions as helper, it stores the
 * dictionary of all conversions and resolves the data using it
 *
 * @fn  => the caller function
 * @arg => the argument passed to the function
 */
function __apps($fn, $arg)
{
    static $dict = [];
    if (!count($dict)) {
        $query = "SELECT * FROM tbl_apps WHERE active = 1";
        $result = db_query($query);
        $dict["id2app"] = [];
        $dict["app2id"] = [];
        $dict["id2table"] = [];
        $dict["app2table"] = [];
        $dict["table2id"] = [];
        $dict["table2app"] = [];
        $dict["id2subtables"] = [];
        $dict["app2subtables"] = [];
        $dict["table2subtables"] = [];
        $dict["app2index"] = [];
        $dict["app2control"] = [];
        $dict["app2version"] = [];
        $dict["app2files"] = [];
        $dict["app2notes"] = [];
        $dict["subtable2id"] = [];
        $dict["subtable2app"] = [];
        $dict["subtable2table"] = [];
        while ($row = db_fetch_row($result)) {
            $row["subtables"] = __apps_subtables_helper($row["subtables"]);
            $dict["id2app"][$row["id"]] = $row["code"];
            $dict["app2id"][$row["code"]] = $row["id"];
            $dict["id2table"][$row["id"]] = $row["table"];
            $dict["app2table"][$row["code"]] = $row["table"];
            $dict["table2id"][$row["table"]] = $row["id"];
            $dict["table2app"][$row["table"]] = $row["code"];
            $dict["id2subtables"][$row["id"]] = $row["subtables"];
            $dict["app2subtables"][$row["code"]] = $row["subtables"];
            $dict["table2subtables"][$row["table"]] = $row["subtables"];
            $dict["app2index"][$row["code"]] = $row["has_index"];
            $dict["app2control"][$row["code"]] = $row["has_control"];
            $dict["app2version"][$row["code"]] = $row["has_version"];
            $dict["app2files"][$row["code"]] = $row["has_files"];
            $dict["app2notes"][$row["code"]] = $row["has_notes"];
            foreach ($row["subtables"] as $subtable) {
                $dict["subtable2id"][$subtable["subtable"]] = $row["id"];
                $dict["subtable2app"][$subtable["subtable"]] = $row["code"];
                $dict["subtable2table"][$subtable["subtable"]] = $row["table"];
            }
        }
        db_free($result);
    }
    if ($fn == "app_exists") {
        return isset($dict["app2id"][$arg]);
    }
    if ($fn == "table_exists") {
        return isset($dict["table2id"][$arg]);
    }
    if ($fn == "subtable_exists") {
        return isset($dict["subtable2id"][$arg]);
    }
    if (isset($dict[$fn][$arg])) {
        return $dict[$fn][$arg];
    }
    show_php_error(["phperror" => "$fn($arg) not found"]);
}

/**
 * Subtables helper
 *
 * This function helps the follow apps to returns the subtables information into
 * a structure instead of string, this is very helpfull by the code that consumes
 * this information, allow to use it quickly without parsing the original string.
 *
 * @subtables => the string that contains the subtables specification
 */
function __apps_subtables_helper($subtables)
{
    $subtables = array_diff(explode(",", $subtables), [""]);
    foreach ($subtables as $key => $val) {
        if (strpos($val, ":")) {
            $alias = strtok($val, ":");
            $subtable = strtok("(");
        } else {
            $alias = "";
            $subtable = strtok($val, "(");
        }
        $field = strtok(")");
        $subtables[$key] = [
            "alias" => $alias,
            "subtable" => $subtable,
            "field" => $field,
        ];
    }
    return $subtables;
}

/**
 * Id to App
 *
 * This function resolves the code of the app from the app id
 *
 * @id => the app id used to resolve the code
 */
function id2app($id)
{
    return __apps(__FUNCTION__, $id);
}

/**
 * App to Id
 *
 * This function resolves the id of the app from the app code
 *
 * @app => the code used to resolve the id
 */
function app2id($app)
{
    return __apps(__FUNCTION__, $app);
}

/**
 * Id to Table
 *
 * This function resolves the table of the app from the app id
 *
 * @id => the app id used to resolve the table
 */
function id2table($id)
{
    return __apps(__FUNCTION__, $id);
}

/**
 * App to Table
 *
 * This function resolves the table of the app from the app code
 *
 * @app => the app code used to resolve the table
 */
function app2table($app)
{
    return __apps(__FUNCTION__, $app);
}

/**
 * Table to Id
 *
 * This function resolves the id of the app from the app table
 *
 * @table => the app table used to resolve the id
 */
function table2id($table)
{
    return __apps(__FUNCTION__, $table);
}

/**
 * Table to App
 *
 * This function resolves the code of the app from the app table
 *
 * @table => the app table used to resolve the app code
 */
function table2app($table)
{
    return __apps(__FUNCTION__, $table);
}

/**
 * Id to Subtables
 *
 * This function resolves the subtables of the app from the app id
 *
 * @id => the app id used to resolve the subtables
 */
function id2subtables($id)
{
    return __apps(__FUNCTION__, $id);
}

/**
 * App to Subtables
 *
 * This function resolves the subtables of the app from the app code
 *
 * @app => the app code used to resolve the subtables
 */
function app2subtables($app)
{
    return __apps(__FUNCTION__, $app);
}

/**
 * Table to Subtables
 *
 * This function resolves the subtables of the app from the app table
 *
 * @table => the app table used to resolve the subtables
 */
function table2subtables($table)
{
    return __apps(__FUNCTION__, $table);
}

/**
 * App Exists
 *
 * This function detect if an app exists
 *
 * @app => the app that you want to check if exists
 */
function app_exists($app)
{
    return __apps(__FUNCTION__, $app);
}

/**
 * Detect apps files
 *
 * This function returns the files found in the main path and in the apps path
 *
 * @file => the pattern used to search files
 */
function detect_apps_files($file)
{
    $files = array_merge(glob($file), glob("apps/*/{$file}"));
    return $files;
}

/**
 * App to Index
 *
 * This function returns the has_index of the app
 *
 * @app => the app code used to resolve the index
 */
function app2index($app)
{
    return __apps(__FUNCTION__, $app);
}

/**
 * App to Control
 *
 * This function returns the has_control of the app
 *
 * @app => the app code used to resolve the control
 */
function app2control($app)
{
    return __apps(__FUNCTION__, $app);
}

/**
 * App to Version
 *
 * This function returns the has_version of the app
 *
 * @app => the app code used to resolve the version
 */
function app2version($app)
{
    return __apps(__FUNCTION__, $app);
}

/**
 * App to Files
 *
 * This function returns the has_files of the app
 *
 * @app => the app code used to resolve the files
 */
function app2files($app)
{
    return __apps(__FUNCTION__, $app);
}

/**
 * App to Notes
 *
 * This function returns the has_notes of the app
 *
 * @app => the app code used to resolve the notes
 */
function app2notes($app)
{
    return __apps(__FUNCTION__, $app);
}

/**
 * Current app
 *
 * This function returns the id of the current app
 */
function current_app()
{
    if (get_data("rest/0") != "app") {
        show_php_error(["phperror" => "unknown app in rest args"]);
    }
    return app2id(get_data("rest/1"));
}

/**
 * Subtable to Id
 *
 * This function resolves the id of the app from the subtable
 *
 * @subtable => the app subtable used to resolve the id
 */
function subtable2id($subtable)
{
    return __apps(__FUNCTION__, $subtable);
}

/**
 * Subtable to App
 *
 * This function resolves the code of the app from the app subtable
 *
 * @subtable => the app subtable used to resolve the app code
 */
function subtable2app($subtable)
{
    return __apps(__FUNCTION__, $subtable);
}

/**
 * Subtable to table
 *
 * This function resolves the table of the app from the app subtable
 *
 * @subtable => the app subtable used to resolve the table
 */
function subtable2table($subtable)
{
    return __apps(__FUNCTION__, $subtable);
}

/**
 * Table Exists
 *
 * This function detect if a table exists
 *
 * @table => the table that you want to check if exists
 */
function table_exists($table)
{
    return __apps(__FUNCTION__, $table);
}

/**
 * Subtable Exists
 *
 * This function detect if a subtable exists
 *
 * @subtable => the subtable that you want to check if exists
 */
function subtable_exists($subtable)
{
    return __apps(__FUNCTION__, $subtable);
}
