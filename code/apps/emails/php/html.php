<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2025 by Josep Sanz Campderrós
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
 * Html helper module
 *
 * This file contain useful html helper functions
 */

/**
 * Defines section
 *
 * This defines allow to define some useful needed resources by this file.
 */
define('__GIF_IMAGE__', 'data:image/gif;base64,R0lGODlhCgABAIABAOns7////ywAAAAACgABAAACA4SPBQA7');
define('__CHARS_MAP__', ['&' => '&amp;', '+' => '&#43;']);

/**
 * Remove Script Tag
 *
 * This function tries to remove all <script> tags of the string
 *
 * @temp => the string that you want to process
 */
function remove_script_tag($html)
{
    $html = preg_replace('@<script[^>]*?.*?</script>@siu', '', $html);
    return $html;
}

/**
 * Remove Style Tag
 *
 * This function tries to remove all <style> tags of the string
 *
 * @temp => the string that you want to process
 */
function remove_style_tag($html)
{
    $html = preg_replace('@<style\b[^>]*?.*?</style>@siu', '', $html);
    return $html;
}

/**
 * Remove Comment Tag
 *
 * This function tries to remove all <!-- --> tags of the string
 *
 * @temp => the string that you want to process
 */
function remove_comment_tag($html)
{
    $html = preg_replace('@<!--[^>]*?.*?-->@siu', '', $html);
    return $html;
}

/**
 * Remove Meta Tag
 *
 * This function tries to remove all <meta > tags of the string
 *
 * @temp => the string that you want to process
 */
function remove_meta_tag($html)
{
    if (trim($html) == '') {
        return $html;
    }
    $dom = new DOMDocument();
    libxml_use_internal_errors(true); // Trick
    $dom->loadHTML($html);
    libxml_clear_errors(); // Trick
    $items = $dom->getElementsByTagName('meta');
    foreach ($items as $item) {
        foreach ($item->attributes as $attribute) {
            if (strpos($attribute->value, '>') !== false) {
                $from = $attribute->value;
                $to = str_replace('>', '', $attribute->value);
                $html = str_replace($from, $to, $html);
            }
        }
    }
    $html = preg_replace('@<meta\b[^>]*?.*?>@siu', '', $html);
    return $html;
}

/**
 * Remove Link Tag
 *
 * This function tries to remove all <link > tags of the string
 *
 * @temp => the string that you want to process
 */
function remove_link_tag($html)
{
    if (trim($html) == '') {
        return $html;
    }
    $dom = new DOMDocument();
    libxml_use_internal_errors(true); // Trick
    $dom->loadHTML($html);
    libxml_clear_errors(); // Trick
    $items = $dom->getElementsByTagName('link');
    foreach ($items as $item) {
        foreach ($item->attributes as $attribute) {
            if (strpos($attribute->value, '>') !== false) {
                $from = $attribute->value;
                $to = str_replace('>', '', $attribute->value);
                $html = str_replace($from, $to, $html);
            }
        }
    }
    $html = preg_replace('@<link\b[^>]*?.*?>@siu', '', $html);
    return $html;
}

/**
 * Inline Img Tag
 *
 * This function tries to convert all imgs to an inline imgs
 *
 * @temp => the string that you want to process
 */
function inline_img_tag($html)
{
    if (trim($html) == '') {
        return $html;
    }
    $dom = new DOMDocument();
    libxml_use_internal_errors(true); // Trick
    $dom->loadHTML($html);
    libxml_clear_errors(); // Trick
    $items = $dom->getElementsByTagName('img');
    foreach ($items as $item) {
        $src = $item->getAttribute('src');
        $img = __inline_img_helper($src);
        if ($img == $src) {
            continue;
        }
        $froms = [
            $src,
            str_replace_assoc(__CHARS_MAP__, $src),
            htmlspecialchars($src),
        ];
        $froms = array_unique($froms);
        foreach ($froms as $from) {
            $html = str_replace_one($from, $img, $html);
        }
    }
    return $html;
}

/**
 * Inline Img Style
 *
 * This function tries to convert all imgs to an inline imgs
 *
 * @temp => the string that you want to process
 */
function inline_img_style($html)
{
    if (trim($html) == '') {
        return $html;
    }
    $dom = new DOMDocument();
    libxml_use_internal_errors(true); // Trick
    $dom->loadHTML($html);
    libxml_clear_errors(); // Trick
    $items = $dom->getElementsByTagName('*');
    foreach ($items as $item) {
        $style = $item->getAttribute('style');
        preg_match_all('/url\((.*?)\)/', $style, $matches);
        if (!count($matches[1])) {
            continue;
        }
        foreach ($matches[1] as $src) {
            if (in_array(substr($src, 0, 1), ['"', "'"])) {
                $src = substr($src, 1);
            }
            if (in_array(substr($src, -1, 1), ['"', "'"])) {
                $src = substr($src, 0, -1);
            }
            $img = __inline_img_helper($src);
            if ($img == $src) {
                continue;
            }
            $froms = [
                $src,
                str_replace_assoc(__CHARS_MAP__, $src),
                htmlspecialchars($src),
            ];
            $froms = array_unique($froms);
            foreach ($froms as $from) {
                $html = str_replace_one($from, $img, $html);
            }
        }
    }
    return $html;
}

/**
 * Inline Img Background
 *
 * This function tries to convert all imgs to an inline imgs
 *
 * @temp => the string that you want to process
 */
function inline_img_background($html)
{
    if (trim($html) == '') {
        return $html;
    }
    $dom = new DOMDocument();
    libxml_use_internal_errors(true); // Trick
    $dom->loadHTML($html);
    libxml_clear_errors(); // Trick
    $items = $dom->getElementsByTagName('*');
    foreach ($items as $item) {
        $src = $item->getAttribute('background');
        if ($src == '') {
            continue;
        }
        $img = __inline_img_helper($src);
        if ($img == $src) {
            continue;
        }
        $froms = [
            $src,
            str_replace_assoc(__CHARS_MAP__, $src),
            htmlspecialchars($src),
        ];
        $froms = array_unique($froms);
        foreach ($froms as $from) {
            $html = str_replace_one($from, $img, $html);
        }
    }
    return $html;
}

/**
 * TODO
 *
 * TODO
 */
function __inline_img_helper($src)
{
    $src = trim($src);
    $scheme = parse_url($src, PHP_URL_SCHEME);
    if (!in_array($scheme, ['https', 'http'])) {
        return $src;
    }
    $cache = get_cache_file($src, '.b64');
    if (file_exists($cache)) {
        return mime_inline('file/b64', basename($cache));
    }
    $error = get_cache_file($src, '.err');
    if (file_exists($error)) {
        return __GIF_IMAGE__;
    }
    // headers added to solve akamai 403 forbidden error
    $data = __url_get_contents($src, [
        'headers' => [
            'User-Agent' => get_data('server/user_agent'),
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => get_data('server/lang'),
            'Accept-Encoding' => 'gzip, deflate, br, zstd',
        ],
    ]);
    if ($data['code'] == 200) {
        $key = array_key_search('content-type', $data['headers']);
        if (isset($data['headers'][$key])) {
            $type = $data['headers'][$key];
        } else {
            $type = saltos_content_type_from_string($data['body']);
        }
        $type0 = saltos_content_type0($type);
        if (in_array($type0, ['image', 'application'])) {
            $hash1 = md5($data['body']);
            require_once 'php/lib/gdlib.php';
            $data['body'] = image_resize($data['body'], 1000);
            $hash2 = md5($data['body']);
            if ($hash1 != $hash2) {
                $type = 'image/jpeg';
            }
            $img = mime_inline($type, $data['body']);
            file_put_contents($cache, $img);
            chmod_protected($cache, 0666);
            return mime_inline('file/b64', basename($cache));
        }
    }
    file_put_contents($error, sprintr([
        'src' => $src,
        'data' => $data,
    ]));
    chmod_protected($cache, 0666);
    return __GIF_IMAGE__;
}

/**
 * TODO
 *
 * TODO
 */
function extract_img_tag($html)
{
    if (trim($html) == '') {
        return [$html, []];
    }
    $dom = new DOMDocument();
    libxml_use_internal_errors(true); // Trick
    $dom->loadHTML($html);
    libxml_clear_errors(); // Trick
    $items = $dom->getElementsByTagName('img');
    $files = [];
    foreach ($items as $item) {
        $src = $item->getAttribute('src');
        $img = mime_extract(__inline_img_helper($src));
        if ($img['data'] == '' || $img['type'] == '') {
            continue;
        }
        $hash = md5($img['data']);
        $files[$hash] = $img;
        $html = str_replace_one($src, "cid:$hash", $html);
    }
    return [$html, $files];
}

/**
 * TODO
 *
 * TODO
 */
function extract_img_style($html)
{
    if (trim($html) == '') {
        return [$html, []];
    }
    $dom = new DOMDocument();
    libxml_use_internal_errors(true); // Trick
    $dom->loadHTML($html);
    libxml_clear_errors(); // Trick
    $items = $dom->getElementsByTagName('*');
    $files = [];
    foreach ($items as $item) {
        $style = $item->getAttribute('style');
        preg_match_all('/url\((.*?)\)/', $style, $matches);
        if (!count($matches[1])) {
            continue;
        }
        foreach ($matches[1] as $src) {
            if (in_array(substr($src, 0, 1), ['"', "'"])) {
                $src = substr($src, 1);
            }
            if (in_array(substr($src, -1, 1), ['"', "'"])) {
                $src = substr($src, 0, -1);
            }
            $img = mime_extract(__inline_img_helper($src));
            if ($img['data'] == '' || $img['type'] == '') {
                continue;
            }
            $hash = md5($img['data']);
            $files[$hash] = $img;
            $html = str_replace_one($src, "cid:$hash", $html);
        }
    }
    return [$html, $files];
}

/**
 * TODO
 *
 * TODO
 */
function extract_img_background($html)
{
    if (trim($html) == '') {
        return [$html, []];
    }
    $dom = new DOMDocument();
    libxml_use_internal_errors(true); // Trick
    $dom->loadHTML($html);
    libxml_clear_errors(); // Trick
    $items = $dom->getElementsByTagName('*');
    $files = [];
    foreach ($items as $item) {
        $src = $item->getAttribute('background');
        if ($src == '') {
            continue;
        }
        $img = mime_extract(__inline_img_helper($src));
        if ($img['data'] == '' || $img['type'] == '') {
            continue;
        }
        $hash = md5($img['data']);
        $files[$hash] = $img;
        $html = str_replace_one($src, "cid:$hash", $html);
    }
    return [$html, $files];
}

/**
 * TODO
 *
 * TODO
 */
function fix_img_tag($html)
{
    if (trim($html) == '') {
        return $html;
    }
    $dom = new DOMDocument();
    libxml_use_internal_errors(true); // Trick
    $dom->loadHTML($html);
    libxml_clear_errors(); // Trick
    $items = $dom->getElementsByTagName('img');
    foreach ($items as $item) {
        $src = $item->getAttribute('src');
        $scheme = parse_url($src, PHP_URL_SCHEME);
        if (in_array($scheme, ['data'])) {
            continue;
        }
        $froms = [
            $src,
            str_replace_assoc(__CHARS_MAP__, $src),
            htmlspecialchars($src),
        ];
        $froms = array_unique($froms);
        foreach ($froms as $from) {
            $html = str_replace_one($from, __GIF_IMAGE__, $html);
        }
    }
    return $html;
}

/**
 * TODO
 *
 * TODO
 */
function fix_img_style($html)
{
    if (trim($html) == '') {
        return $html;
    }
    $dom = new DOMDocument();
    libxml_use_internal_errors(true); // Trick
    $dom->loadHTML($html);
    libxml_clear_errors(); // Trick
    $items = $dom->getElementsByTagName('*');
    foreach ($items as $item) {
        $style = $item->getAttribute('style');
        preg_match_all('/url\((.*?)\)/', $style, $matches);
        if (!count($matches[1])) {
            continue;
        }
        foreach ($matches[1] as $src) {
            if (in_array(substr($src, 0, 1), ['"', "'"])) {
                $src = substr($src, 1);
            }
            if (in_array(substr($src, -1, 1), ['"', "'"])) {
                $src = substr($src, 0, -1);
            }
            $scheme = parse_url($src, PHP_URL_SCHEME);
            if (in_array($scheme, ['data'])) {
                continue;
            }
            $froms = [
                $src,
                str_replace_assoc(__CHARS_MAP__, $src),
                htmlspecialchars($src),
            ];
            $froms = array_unique($froms);
            foreach ($froms as $from) {
                $html = str_replace_one($from, __GIF_IMAGE__, $html);
            }
        }
    }
    return $html;
}

/**
 * TODO
 *
 * TODO
 */
function fix_img_background($html)
{
    if (trim($html) == '') {
        return $html;
    }
    $dom = new DOMDocument();
    libxml_use_internal_errors(true); // Trick
    $dom->loadHTML($html);
    libxml_clear_errors(); // Trick
    $items = $dom->getElementsByTagName('*');
    foreach ($items as $item) {
        $src = $item->getAttribute('background');
        if ($src == '') {
            continue;
        }
        $scheme = parse_url($src, PHP_URL_SCHEME);
        if (in_array($scheme, ['data'])) {
            continue;
        }
        $froms = [
            $src,
            str_replace_assoc(__CHARS_MAP__, $src),
            htmlspecialchars($src),
        ];
        $froms = array_unique($froms);
        foreach ($froms as $from) {
            $html = str_replace_one($from, __GIF_IMAGE__, $html);
        }
    }
    return $html;
}

/**
 * TODO
 *
 * TODO
 *
 * Notes:
 *
 * We are using {48} to force to search a base64 data of 48 of bytes, where decoded
 * must to be a 36 bytes string, this match with the cache style file like a md5 hash
 * of 32 bytes with 4 bytes more by the extension file (.b64)
 *
 */
function fix_file_b64($html)
{
    $dir = get_directory('dirs/cachedir') ?? getcwd_protected() . '/data/cache/';
    preg_match_all('/data:file\/b64;base64,[a-zA-Z0-9\+\/=]{48}/', $html, $matches);
    foreach ($matches[0] as $mime) {
        $file = mime_extract($mime)['data'];
        if (file_exists($dir . $file)) {
            $img = file_get_contents($dir . $file);
            $html = str_replace_one($mime, $img, $html);
        }
    }
    return $html;
}
