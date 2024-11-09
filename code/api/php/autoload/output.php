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
 * Output helper module
 *
 * This fie contains useful functions related to the output of the SaltOS, allow to send contents to
 * the clients using the specified format and configuration, useful to return contents, too implement
 * a specific output for the json format that is the most format used by the new SaltOS
 */

/**
 * Output Handler
 *
 * This function is intended to send data to the output channel, and can have
 * the follow arguments:
 *
 * @array => array with the follow pairs of key val
 * @file  => file that contains the contents that you want to send
 * @data  => contents that you want to send to the output channel
 * @type  => content type header used
 * @cache => boolean to enable the cache usage, includes the etag algorithm
 * @name  => the filename used in the content disposition attachment header
 * @extra => headers that you can add to the transfer
 */
function output_handler($array)
{
    $file = isset($array['file']) ? $array['file'] : '';
    $data = isset($array['data']) ? $array['data'] : '';
    $type = isset($array['type']) ? $array['type'] : '';
    $cache = isset($array['cache']) ? $array['cache'] : '';
    $name = isset($array['name']) ? $array['name'] : '';
    $extra = isset($array['extra']) ? $array['extra'] : [];
    if ($file != '') {
        if (!file_exists($file) || !is_file($file)) {
            show_php_error(['phperror' => "file {$file} not found"]);
        }
        if ($data == '' && filesize($file) < memory_get_free(true) / 3) {
            $data = file_get_contents($file);
        }
        if ($type == '') {
            $type = saltos_content_type($file);
        }
    }
    if ($type === '') {
        show_php_error(['phperror' => 'output_handler requires the type parameter']);
    }
    if ($cache === '') {
        show_php_error(['phperror' => 'output_handler requires the cache parameter']);
    }
    __output_header('About: ' . get_name_version_revision());
    if ($cache) {
        $hash1 = get_server('HTTP_IF_NONE_MATCH');
        if ($file != '' && $data == '') {
            $hash2 = md5_file($file);
        } else {
            $hash2 = md5($data);
        }
        if ($hash1 == $hash2) {
            __output_header('HTTP/1.1 304 Not Modified');
            pcov_stop();
            // @codeCoverageIgnoreStart
            die();
            // @codeCoverageIgnoreEnd
        }
    }
    if ($file != '' && $data == '') {
        __output_header('Content-Encoding: none');
    } else {
        $encoding = strval(get_server('HTTP_ACCEPT_ENCODING'));
        if (stripos($encoding, 'zstd') !== false && function_exists('zstd_compress')) {
            __output_header('Content-Encoding: zstd');
            $data = zstd_compress($data);
        } elseif (stripos($encoding, 'gzip') !== false && function_exists('gzencode')) {
            __output_header('Content-Encoding: gzip');
            $data = gzencode($data);
        } elseif (stripos($encoding, 'deflate') !== false && function_exists('gzdeflate')) {
            __output_header('Content-Encoding: deflate');
            $data = gzdeflate($data);
        } else {
            __output_header('Content-Encoding: none');
        }
        __output_header('Vary: Accept-Encoding');
    }
    if ($file != '' && $data == '') {
        $size = filesize($file);
    } else {
        $size = strlen($data);
    }
    if ($cache) {
        __output_header(
            'Expires: ' . gmdate('D, d M Y H:i:s', time() + get_config('server/cachetimeout')) . ' GMT'
        );
        __output_header('Cache-Control: max-age=' . get_config('server/cachetimeout') . ', no-transform');
        __output_header('Pragma: public');
        __output_header("ETag: {$hash2}");
    } else {
        __output_header('Expires: -1');
        __output_header(
            'Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0, no-transform'
        );
        __output_header('Pragma: no-cache');
    }
    __output_header("Content-Type: {$type}");
    __output_header("Content-Length: {$size}");
    if ($name != '') {
        __output_header("Content-disposition: attachment; filename=\"{$name}\"");
    }
    foreach ($extra as $temp) {
        __output_header($temp, false);
    }
    __output_header('Connection: keep-alive, close');
    if ($file != '' && $data == '') {
        readfile($file);
    } else {
        echo $data;
    }
    pcov_stop();
    // @codeCoverageIgnoreStart
    die();
    // @codeCoverageIgnoreEnd
}

/**
 * Output header helper
 *
 * This function is a filter to ignore the headers when CLI SAPI is detected
 *
 * @header  => The header that do you want to send to the client
 * @replace => The boolean used as replace in the original function, true by default
 */
function __output_header($header, $replace = true)
{
    if (get_data('server/request_method') == 'CLI') {
        return;
    }
    header($header, $replace);
}

/**
 * Output Handler JSON
 *
 * This function allow to quickly send json output, the unique argument that it
 * requires is the data that you want to send
 *
 * @array => content to convert to json and send to the output channel
 *
 * Notes:
 *
 * This function is able to generate a pretty output when stdout is connected to
 * a terminal, intended to be used by humans, in other cases, the output will be
 * minified.
 */
function output_handler_json($array)
{
    if (function_exists('posix_isatty') && defined('STDOUT') && posix_isatty(STDOUT)) {
        $data = json_encode($array, JSON_PRETTY_PRINT) . PHP_EOL;
    } else {
        $data = json_encode($array);
    }
    output_handler([
        'data' => $data,
        'type' => 'application/json',
        'cache' => false,
    ]);
}
