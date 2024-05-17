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
 * Mime helper module
 *
 * This file contains the mimetype feature provided by saltos using different techniques
 * suck as the extension file, using the mime_content_type or the finfo_file functions.
 */

/**
 * SaltOS Content Type
 *
 * This function is intended to returns the mime content-type string using different
 * techniques.
 *
 * @file => the file of which you want to know the content-type
 */
function saltos_content_type($file)
{
    static $mimes = [
        "css" => "text/css",
        "js" => "text/javascript",
        "xml" => "text/xml",
        "htm" => "text/html",
        "html" => "text/html",
        "png" => "image/png",
        "bmp" => "image/bmp",
        "tif" => "image/tiff",
        "tiff" => "image/tiff",
        "jpg" => "image/jpeg",
        "jpeg" => "image/jpeg",
        "json" => "application/json",
        "pdf" => "application/pdf",
    ];
    $ext = strtolower(extension($file));
    if (isset($mimes[$ext])) {
        return $mimes[$ext];
    }
    if (function_exists("mime_content_type")) {
        return mime_content_type($file);
    }
    if (function_exists("finfo_file")) {
        return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file);
    }
    return "application/octet-stream";
}

/**
 * SaltOS Content Type first helper
 *
 * This function returns the first part of the content-type, for example, if you
 * pass the string image/jpeg, this function will returns the string image.
 *
 * @mime => the mime that you want to process
 */
function saltos_content_type0($mime)
{
    $mime = explode("/", $mime);
    return array_shift($mime);
}

/**
 * SaltOS Content Type second helper
 *
 * This function returns the second part of the content-type, for example, if you
 * pass the string image/jpeg, this function will returns the string jpeg.
 *
 * @mime => the mime that you want to process
 */
function saltos_content_type1($mime)
{
    $mime = explode("/", $mime);
    return array_pop($mime);
}

/**
 * Mime inline
 *
 * This function returns the inline mime fragment of string that contains the mime
 * and the encoded in base64 data, intended to embed it in img tags, for example.
 *
 * @type => the mime type (image/png for example)
 * @data => the contents of the data that must to be encoded in base64
 */
function mime_inline($type, $data)
{
    return "data:$type;base64," . base64_encode($data);
}
