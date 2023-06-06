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
 *
 */
function get_directory($key, $default = "")
{
    if (!$default) {
        $default = getcwd_protected() . "/data/temp";
    }
    $dir = get_default($key, $default);
    if (is_array($dir)) {
        $dir = eval_attr($dir);
    }
    if (substr($dir, -1, 1) != "/") {
        $dir .= "/";
    }
    return $dir;
}

/*
 *
 */
function get_temp_file($ext = "")
{
    if ($ext == "") {
        $ext = ".tmp";
    }
    if (substr($ext, 0, 1) != ".") {
        $ext = "." . $ext;
    }
    $dir = get_directory("dirs/tempdir", getcwd_protected() . "/data/temp");
    while (1) {
        $uniqid = get_unique_id_md5();
        $file = $dir . $uniqid . $ext;
        if (!file_exists($file)) {
            break;
        }
    }
    return $file;
}

/*
 *
 */
function get_cache_file($data, $ext = "")
{
    if (is_array($data)) {
        $data = serialize($data);
    }
    if ($ext == "") {
        $ext = strtolower(extension($data));
    }
    if ($ext == "") {
        $ext = ".tmp";
    }
    if (substr($ext, 0, 1) != ".") {
        $ext = "." . $ext;
    }
    $dir = get_directory("dirs/cachedir", getcwd_protected() . "/data/cache");
    $file = $dir . md5($data) . $ext;
    return $file;
}

/*
 *
 */
function cache_exists($cache, $file)
{
    if (!file_exists($cache) || !is_file($cache)) {
        return 0;
    }
    $mtime1 = filemtime($cache);
    if (!is_array($file)) {
        $file = array($file);
    }
    foreach ($file as $f) {
        if (!file_exists($f) || !is_file($f)) {
            return 0;
        }
        $mtime2 = filemtime($f);
        if ($mtime2 >= $mtime1) {
            return 0;
        }
    }
    return 1;
}

/*
 *
 */
function url_get_contents($url)
{
    // CHECK SCHEME
    $scheme = parse_url($url, PHP_URL_SCHEME);
    if (!$scheme) {
        $url = "http://" . $url;
    }
    // DO THE REQUEST
    list($body,$headers,$cookies) = __url_get_contents($url);
    // RETURN RESPONSE
    return $body;
}

/*
 *
 */
function __url_get_contents($url, $args = array())
{
    require_once "lib/httpclient/http.php";
    $http = new http_class();
    $http->user_agent = get_name_version_revision();
    $http->follow_redirect = 1;
    if (isset($args["cookies"])) {
        $http->RestoreCookies($args["cookies"]);
    }
    $arguments = array();
    $error = $http->GetRequestArguments($url, $arguments);
    if ($error != "") {
        return array("",array(),array());
    }
    $error = $http->Open($arguments);
    if ($error != "") {
        return array("",array(),array());
    }
    if (isset($args["method"])) {
        $arguments["RequestMethod"] = strtoupper($args["method"]);
    }
    if (isset($args["values"])) {
        $arguments["PostValues"] = $args["values"];
    }
    if (isset($args["referer"])) {
        $arguments["Referer"] = $args["referer"];
    }
    if (isset($args["headers"])) {
        foreach ($args["headers"] as $key => $val) {
            $arguments["Headers"][$key] = $val;
        }
    }
    if (isset($args["body"])) {
        $arguments["Body"] = $args["body"];
    }
    $error = $http->SendRequest($arguments);
    if ($error != "") {
        return array("",array(),array());
    }
    $headers = array();
    $error = $http->ReadReplyHeaders($headers);
    if ($error != "") {
        return array("",array(),array());
    }
    $body = "";
    $error = $http->ReadWholeReplyBody($body);
    if ($error != "") {
        return array("",array(),array());
    }
    $http->Close();
    $cookies = array();
    $http->SaveCookies($cookies);
    return array($body,$headers,$cookies);
}

/*
 *
 */
function extension($file)
{
    return pathinfo($file, PATHINFO_EXTENSION);
}

/*
 *
 */
function encode_bad_chars_file($file)
{
    $file = strrev($file);
    $file = explode(".", $file, 2);
    foreach ($file as $key => $val) {
        // EXISTS MULTIPLE STRREV TO PREVENT UTF8 DATA LOST
        $file[$key] = strrev(encode_bad_chars(strrev($val)));
    }
    $file = implode(".", $file);
    $file = strrev($file);
    return $file;
}

/*
 *
 */
function realpath_protected($path)
{
    // REALPATH NO RETORNA RES SI EL PATH NO EXISTEIX
    // ES FA SERVIR QUAN ES VOL EL REALPATH D'UN FITXER QUE ENCARA NO EXISTEIX
    // PER EXEMPLE, PER LA SORTIDA D'UNA COMANDA
    return realpath(dirname($path)) . "/" . basename($path);
}

/*
 *
 */
function getcwd_protected()
{
    $dir = getcwd();
    if ($dir == "/") {
        $dir = dirname(get_server("SCRIPT_FILENAME"));
    }
    return $dir;
}

/*
 *
 */
function glob_protected($pattern)
{
    $array = glob($pattern);
    return is_array($array) ? $array : array();
}

/*
 *
 */
function chmod_protected($file, $mode)
{
    if ((fileperms($file) & 0777) != $mode) {
        chmod($file, $mode);
    }
}

/*
 *
 */
// ESTA FUNCION SE USA POR LA LIBRERIA HTTPCLIENT
function fsockopen_protected($hostname, $port, &$errno = 0, &$errstr = "", $timeout = null)
{
    if ($timeout == null) {
        $timeout = ini_get("default_socket_timeout");
    }
    return stream_socket_client(
        $hostname . ":" . $port,
        $errno,
        $errstr,
        $timeout,
        STREAM_CLIENT_CONNECT,
        stream_context_create(
            array(
                "ssl" => array(
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                    "allow_self_signed" => true
                )
            )
        )
    );
}
