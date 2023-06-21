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
 * Output Handler
 *
 * This function is intended to send data to the output channel, and can have
 * the follow arguments:
 *
 * @array => array with the follow pairs of key val
 * @file => file that contains the contents that you want to send
 * @data => contents that you want to send to the output channel
 * @type => content type header used
 * @cache => boolean to enable the cache usage, includes the etag algorithm
 * @name => the filename used in the content disposition attachment header
 * @extra => headers that you can add to the transfer
 */
function output_handler($array)
{
    $file = isset($array["file"]) ? $array["file"] : "";
    $data = isset($array["data"]) ? $array["data"] : "";
    $type = isset($array["type"]) ? $array["type"] : "";
    $cache = isset($array["cache"]) ? $array["cache"] : "";
    $name = isset($array["name"]) ? $array["name"] : "";
    $extra = isset($array["extra"]) ? $array["extra"] : array();
    if ($file != "") {
        if (!file_exists($file) || !is_file($file)) {
            show_php_error(array("phperror" => "file {$file} not found"));
        }
        if ($data == "" && filesize($file) < memory_get_free(true) / 3) {
            $data = file_get_contents($file);
        }
        if ($type == "") {
            $type = saltos_content_type($file);
        }
    }
    if ($type === "") {
        show_php_error(array("phperror" => "output_handler requires the type parameter"));
    }
    if ($cache === "") {
        show_php_error(array("phperror" => "output_handler requires the cache parameter"));
    }
    header("X-Powered-By: " . get_name_version_revision());
    if ($cache) {
        $hash1 = get_server("HTTP_IF_NONE_MATCH");
        if ($file != "" && $data == "") {
            $hash2 = md5_file($file);
        } else {
            $hash2 = md5($data);
        }
        if ($hash1 == $hash2) {
            header("HTTP/1.1 304 Not Modified");
            die();
        }
    }
    if ($file != "" && $data == "") {
        header("Content-Encoding: none");
    } else {
        $encoding = get_server("HTTP_ACCEPT_ENCODING");
        if (stripos($encoding, "gzip") !== false && function_exists("gzencode")) {
            header("Content-Encoding: gzip");
            $data = gzencode($data);
        } elseif (stripos($encoding, "deflate") !== false && function_exists("gzdeflate")) {
            header("Content-Encoding: deflate");
            $data = gzdeflate($data);
        } else {
            header("Content-Encoding: none");
        }
        header("Vary: Accept-Encoding");
    }
    if ($file != "" && $data == "") {
        $size = filesize($file);
    } else {
        $size = strlen($data);
    }
    if ($cache) {
        header("Expires: " . gmdate("D, d M Y H:i:s", time() + get_config("server/cachetimeout")) . " GMT");
        header("Cache-Control: max-age=" . get_config("server/cachetimeout") . ", no-transform");
        header("Pragma: public");
        header("ETag: {$hash2}");
    } else {
        header("Expires: -1");
        header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0, no-transform");
        header("Pragma: no-cache");
    }
    header("Content-Type: {$type}");
    header("Content-Length: {$size}");
    if ($name != "") {
        header("Content-disposition: attachment; filename=\"{$name}\"");
    }
    foreach ($extra as $temp) {
        header($temp, false);
    }
    header("Connection: keep-alive, close");
    if ($file != "" && $data == "") {
        readfile($file);
    } else {
        echo $data;
    }
    die();
}

/**
 * Output Handler JSON
 *
 * This function allow to quickly send json output, the unique argument that it
 * requires is the data that you want to send
 *
 * @array => content to convert to json and send to the output channel
 */
function output_handler_json($array)
{
    output_handler(array(
        "data" => json_encode($array),
        "type" => "application/json",
        "cache" => false
    ));
}
