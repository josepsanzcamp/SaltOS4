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
 * File utils helper module
 *
 * This fie contains useful functions related to the file usage, allow to manage directories, files,
 * caches, paths, permissions, remote files and more
 */

/**
 * Get Directory
 *
 * This function returns the directory configured to the key requested, too can define a default
 * value, useful when the configuration still not loaded and SaltOS need some directory to do
 * something as store data in the log file, for example
 *
 * @key => the key used in get_config to request the configured directory
 */
function get_directory($key)
{
    $dir = get_config($key);
    if ($dir === null) {
        return $dir;
    }
    if (is_array($dir)) {
        $dir = eval_attr($dir);
    }
    if (substr($dir, -1, 1) != "/") {
        $dir .= "/";
    }
    return $dir;
}

/**
 * Get Temp File
 *
 * This function is intended to get a unique temporary file, used for temporary
 * purposes as put contents to be used as input in a command
 *
 * @ext => the extension of the temporary file, useful for some commands that
 *         try to detect the contents using the extension
 *
 * Notes:
 *
 * This function uses the dirs/tempdir config key
 */
function get_temp_file($ext = "")
{
    if ($ext == "") {
        $ext = ".tmp";
    }
    if (substr($ext, 0, 1) != ".") {
        $ext = "." . $ext;
    }
    $dir = get_directory("dirs/tempdir") ?? getcwd_protected() . "/data/temp/";
    while (1) {
        $uniqid = get_unique_id_md5();
        $file = $dir . $uniqid . $ext;
        if (!file_exists($file)) {
            break;
        }
    }
    return $file;
}

/**
 * Get Cache File
 *
 * This function is intended to get a cache filename, used for caching purposes
 *
 * @data => data used to compute the hash used by the cache, can be an string or
 *          an array with lot of contents
 * @ext  => extension of the cache filename
 *
 * Notes:
 *
 * This function uses the dirs/cachedir config key
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
    $dir = get_directory("dirs/cachedir") ?? getcwd_protected() . "/data/cache/";
    $file = $dir . md5($data) . $ext;
    return $file;
}

/**
 * Cache Exists
 *
 * This function check the existence of valid cache by comparing the timestamp
 * of the filemtime between the cache file and all files of the second argument
 *
 * @cache => cache filename
 * @files => array of files that are considered as dependencies of the cache
 */
function cache_exists($cache, $files)
{
    if (!file_exists($cache) || !is_file($cache)) {
        return 0;
    }
    $mtime1 = filemtime($cache);
    if (!is_array($files)) {
        $files = [$files];
    }
    foreach ($files as $file) {
        if (!file_exists($file) || !is_file($file)) {
            return 0;
        }
        $mtime2 = filemtime($file);
        if ($mtime2 >= $mtime1) {
            return 0;
        }
    }
    return 1;
}

/**
 * URL Get Contents
 *
 * This file is an equivalent of the file_get_contents but intended to be used
 * for request remote files using protocols as http or https
 *
 * @url => the url that you want to retrieve
 *
 * Notes:
 *
 * This function only returns the body of the request, if you are interested
 * to get the headers of the request, try to use the __url_get_contents
 */
function url_get_contents($url)
{
    // Check scheme
    $scheme = parse_url($url, PHP_URL_SCHEME);
    if (!$scheme) {
        $url = "http://" . $url;
    }
    // Do the request
    $response = __url_get_contents($url);
    // Return response's body
    return $response["body"];
}

/**
 * URL Get Contents helper
 *
 * This file is an equivalent of the file_get_contents but intended to be used
 * for request remote files using protocols as http or https
 *
 * @url     => the url that you want to retrieve
 * @args    => Array of arguments, explained in the follow lines
 * @cookies => an array with the cookies to be restored before send the request
 * @method  => method used in the request
 * @values  => an array with the post values, useful when you want to send a POST
 *             request with pairs of variables and values
 * @referer => the referer string
 * @headers => an array with the headers to be send in the request
 * @body    => the full body used of the request, useful when you want to send a
 *             json file in the body instead of pairs of keys vals
 *
 * This function returns an array with three elements, body, headers and cookies
 *
 * Notes:
 *
 * This function uses the httpclient library
 */
function __url_get_contents($url, $args = [])
{
    require_once "core/lib/httpclient/http.php";
    $http = new http_class();
    $http->user_agent = get_name_version_revision();
    $http->follow_redirect = 1;
    if (isset($args["cookies"])) {
        $http->RestoreCookies($args["cookies"]);
    }
    $arguments = [];
    $error = $http->GetRequestArguments($url, $arguments);
    if ($error != "") {
        return ["", [], []];
    }
    $error = $http->Open($arguments);
    if ($error != "") {
        return ["", [], []];
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
        return ["", [], []];
    }
    $headers = [];
    $error = $http->ReadReplyHeaders($headers);
    if ($error != "") {
        return ["", [], []];
    }
    $body = "";
    $error = $http->ReadWholeReplyBody($body);
    if ($error != "") {
        return ["", [], []];
    }
    $http->Close();
    $cookies = [];
    $http->SaveCookies($cookies);
    return [
        "body" => $body,
        "headers" => $headers,
        "cookies" => $cookies,
    ];
}

/**
 * Extension
 *
 * This function returns the PATHINFO_EXTENSION of the file
 *
 * @file => file used in the pathinfo call
 */
function extension($file)
{
    return pathinfo($file, PATHINFO_EXTENSION);
}

/**
 * Encode Bar Chars File
 *
 * This function is equivalent to encode_bad_chars but intended to be used
 * with filenames, in this case, the extension and the rest of the filename
 * will be encoded separately and the return value will contain the dot
 * separating the filename with the extension
 *
 * @file => filename used in the encode process
 */
function encode_bad_chars_file($file)
{
    $file = strrev($file);
    $file = explode(".", $file, 2);
    foreach ($file as $key => $val) {
        // Exists multiple strrev to prevent UTF8 data lost
        $file[$key] = strrev(encode_bad_chars(strrev($val)));
    }
    $file = implode(".", $file);
    $file = strrev($file);
    return $file;
}

/**
 * Realpath Protected
 *
 * This function returns the realpath of the path, this version of the function
 * allow to return the path of an unexistent file, this is useful when do you
 * want to get the realpath of a unexistent file, for example, to the output of
 * a command that must to generate the file but at the moment of the execution
 * of this function the file is not found
 *
 * @path => path used in the realpath call
 */
function realpath_protected($path)
{
    return realpath(dirname($path)) . "/" . basename($path);
}

/**
 * Getcwd Protected
 *
 * This function returns the same result that the getcwd function but checking
 * that the result is not an slash, this is an issue in some cases caused by
 * permissions problems, and a good solution for this cases is to get the directory
 * of the script as current work directory
 */
function getcwd_protected()
{
    $dir = getcwd();
    if ($dir == "/") {
        $dir = dirname(get_server("SCRIPT_FILENAME"));
    }
    return $dir;
}

/**
 * Glob Protected
 *
 * This function returns the same result that the glob function but checking
 * that the result is an array, if glob fails or not get a files by the pattern,
 * can return other values that an array, and this can cause problems if you are
 * expecting an array to iterate in each element, this function prevent this
 * problem
 *
 * @pattern => pattern used in the glob command
 */
function glob_protected($pattern)
{
    $array = glob($pattern);
    return is_array($array) ? $array : [];
}

/**
 * Chmod Protected
 *
 * This function tries to change the mode of the file using the chmod function
 * only if the fileperms of the file are different that the requested mode
 *
 * @file => file used by the chmod function
 * @mode => mode used by the chmod function
 */
function chmod_protected($file, $mode)
{
    if ((fileperms($file) & 0777) != $mode) {
        chmod($file, $mode);
    }
}

/**
 * Fsockopen Protected
 *
 * This function is only used by the httpclient library to avois problems with
 * the certificates validations
 *
 * Ths arguments is the same that the fsockopen function, in this case, the
 * function uses the stream_socket_client to emulate the original fsockopen
 */
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
            [
                "ssl" => [
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                    "allow_self_signed" => true,
                ],
            ]
        )
    );
}

/**
 * File with Mtime
 *
 * This function returns the name of the file adding as argument the hash
 * of the file for the http/https requests, this allow to helps the browser
 * to know when the file has changed
 *
 * @file => the file that you want to add the hash querystring argument
 */
function file_with_hash($file)
{
    return $file . "?" . md5_file($file);
}
