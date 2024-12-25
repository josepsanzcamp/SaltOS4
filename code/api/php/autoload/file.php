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
    if (is_attr_value($dir)) {
        $dir = eval_attr($dir);
    }
    if ($dir === null) {
        return $dir;
    }
    if ($dir == '') {
        return $dir;
    }
    if (substr($dir, -1, 1) != '/') {
        $dir .= '/';
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
function get_temp_file($ext = '')
{
    if ($ext == '') {
        $ext = '.tmp';
    }
    if (substr($ext, 0, 1) != '.') {
        $ext = '.' . $ext;
    }
    $dir = get_directory('dirs/tempdir') ?? getcwd_protected() . '/data/temp/';
    for (;;) {
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
function get_cache_file($data, $ext = '')
{
    if (is_array($data)) {
        $data = serialize($data);
    }
    if ($ext == '') {
        $ext = strtolower(extension($data));
    }
    if ($ext == '') {
        $ext = '.tmp';
    }
    if (substr($ext, 0, 1) != '.') {
        $ext = '.' . $ext;
    }
    $dir = get_directory('dirs/cachedir') ?? getcwd_protected() . '/data/cache/';
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
        return false;
    }
    $mtime1 = filemtime($cache);
    if (!is_array($files)) {
        $files = [$files];
    }
    foreach ($files as $file) {
        if (!file_exists($file) || !is_file($file)) {
            return false;
        }
        $mtime2 = filemtime($file);
        if ($mtime2 >= $mtime1) {
            return false;
        }
    }
    return true;
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
        $url = 'http://' . $url;
    }
    // Do the request
    $response = __url_get_contents($url);
    // Return response's body
    return $response['body'];
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
 * @_method => method used in the request
 * @values  => an array with the post values, useful when you want to send a POST
 *             request with pairs of variables and values
 * @headers => an array with the headers to be send in the request
 * @body    => the full body used of the request, useful when you want to send a
 *             json file in the body instead of pairs of keys vals
 *
 * This function returns an array with four elements, body, headers, cookies and code
 */
function __url_get_contents($url, $args = [])
{
    $void = [
        'body' => '',
        'headers' => [],
        'cookies' => [],
        'code' => 0,
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_ENABLE_ALPN, false); // to solve cloudflare 403 forbidden
    if (isset($args['cookies'])) {
        $cookies = http_build_query($args['cookies'], '', '; ');
        curl_setopt($ch, CURLOPT_COOKIE, $cookies);
    }
    if (isset($args['method'])) {
        $method = strtoupper($args['method']);
        if ($method == 'HEAD') {
            curl_setopt($ch, CURLOPT_NOBODY, true);
        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }
    }
    if (isset($args['values'])) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $args['values']);
    }
    if (isset($args['body'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $args['body']);
    }
    if (isset($args['headers'])) {
        $headers = [];
        $user_agent = false;
        foreach ($args['headers'] as $key => $val) {
            $key2 = strtolower($key);
            if ($key2 == 'user-agent') {
                curl_setopt($ch, CURLOPT_USERAGENT, $val);
                $user_agent = true;
            } elseif ($key2 == 'referer') {
                curl_setopt($ch, CURLOPT_REFERER, $val);
            } elseif ($key2 == 'accept-encoding') {
                curl_setopt($ch, CURLOPT_ACCEPT_ENCODING, $val);
            } else {
                $headers[] = "$key: $val";
            }
        }
        if (!$user_agent) {
            curl_setopt($ch, CURLOPT_USERAGENT, get_name_version_revision());
        }
        if (count($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
    }
    $response = curl_exec($ch);
    if ($response === false) {
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);
        return array_merge($void, ['error' => "error $errno: $error"]);
    }
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    $headers = substr($response, 0, $size);
    $body = substr($response, $size);
    // parse headers
    $headers = explode("\r\n", trim($headers));
    $temp = [];
    foreach ($headers as $header) {
        $header = explode(': ', $header, 2);
        if (!isset($header[1])) {
            $header[1] = '';
        }
        $header[0] = trim(strtolower($header[0]));
        if ($header[0] == '') {
            continue;
        }
        $header[1] = trim(strtolower($header[1]));
        $temp[$header[0]] = $header[1];
    }
    $headers = $temp;
    // parse cookies
    $cookies = $headers['set-cookie'] ?? '';
    unset($headers['set-cookie']);
    $temp = [];
    if ($cookies == '') {
        $cookies = [];
    } else {
        $cookies = explode(';', $cookies);
    }
    foreach ($cookies as $cookie) {
        $cookie = explode('=', $cookie, 2);
        if (!isset($cookie[1])) {
            $cookie[1] = '';
        }
        $cookie[0] = trim($cookie[0]);
        if ($cookie[0] == '') {
            continue;
        }
        $cookie[1] = trim($cookie[1]);
        $temp[$cookie[0]] = $cookie[1];
    }
    $cookies = $temp;
    // end
    return [
        'body' => $body,
        'headers' => $headers,
        'cookies' => $cookies,
        'code' => $code,
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
    $file = explode('.', $file, 2);
    foreach ($file as $key => $val) {
        // Exists multiple strrev to prevent UTF8 data lost
        $file[$key] = strrev(encode_bad_chars(strrev($val)));
    }
    $file = implode('.', $file);
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
    return realpath(dirname($path)) . '/' . basename($path);
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
    if (in_array($dir, [false, '', '/'])) {
        $dir = dirname(get_server('SCRIPT_FILENAME'));
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
 * only if the fileperms of the file are different that the requested mode and
 * the fileowner of the file is the same user that is executing the script
 *
 * @file => file used by the chmod function
 * @mode => mode used by the chmod function
 */
function chmod_protected($file, $mode)
{
    clearstatcache(); // needed to see changes
    if (!is_readable($file)) {
        return false;
    }
    if ((fileperms($file) & 0777) === $mode) {
        return false;
    }
    if (fileowner($file) !== posix_getuid()) {
        return false;
    }
    chmod($file, $mode);
    return true;
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
function fsockopen_protected($hostname, $port, &$errno = 0, &$errstr = '', $timeout = null)
{
    if ($timeout == null) {
        $timeout = floatval(ini_get('default_socket_timeout'));
    }
    return stream_socket_client(
        $hostname . ':' . $port,
        $errno,
        $errstr,
        $timeout,
        STREAM_CLIENT_CONNECT,
        stream_context_create(
            [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ]
        )
    );
}

/**
 * File with hash
 *
 * This function returns the name of the file adding as argument the hash
 * of the file for the http/https requests, this allow to helps the browser
 * to know when the file has changed
 *
 * @file => the file that you want to add the hash querystring argument
 */
function file_with_hash($file)
{
    return $file . '?' . md5_file($file);
}

/**
 * File with min
 *
 * This function returns the name of the file adding the .min. between the
 * filename and the extension of the file if the .min. file exists
 *
 * @file => the file that you want to add the .min. part if exists
 */
function file_with_min($file)
{
    $ext = extension($file);
    $minfile = str_replace(".$ext", ".min.$ext", $file);
    if (file_exists($minfile)) {
        $file = $minfile;
    }
    return $file;
}

/**
 * File Get Contents Protected
 *
 * This function call the original file_get_contents and returns the buffer
 * returned by the original function, the main idea of this function is to
 * protect the caller to prevent I/O errors like "nohup php index.php" when
 *
 * @args => the original arguments are passed to the file_get_contents
 */
function file_get_contents_protected(...$args)
{
    overload_error_handler('file_get_contents');
    $buffer = file_get_contents(...$args);
    restore_error_handler();
    return $buffer;
}
