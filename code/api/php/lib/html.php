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
 * Html helper module
 *
 * This file contain useful html helper functions
 */

/**
 * Defines section
 *
 * This defines allow to define some useful needed resources by this file.
 */
define('__GIF_IMAGE__', 'data:image/gif;base64,R0lGODdhAQABAIABAOns7wAAACwAAAAAAQABAAACAkQBADs=');
define('__CHARS_MAP__', ['&' => '&amp;', '+' => '&#43;']);

/**
 * Remove Script Tag
 *
 * This function tries to remove all <script> tags of the string
 *
 * @temp => the string that you want to process
 */
function remove_script_tag($temp)
{
    $temp = preg_replace('@<script[^>]*?.*?</script>@siu', '', $temp);
    return $temp;
}

/**
 * Remove Style Tag
 *
 * This function tries to remove all <style> tags of the string
 *
 * @temp => the string that you want to process
 */
function remove_style_tag($temp)
{
    $temp = preg_replace('@<style[^>]*?.*?</style>@siu', '', $temp);
    return $temp;
}

/**
 * Remove Comment Tag
 *
 * This function tries to remove all <!-- --> tags of the string
 *
 * @temp => the string that you want to process
 */
function remove_comment_tag($temp)
{
    $temp = preg_replace('@<!--[^>]*?.*?-->@siu', '', $temp);
    return $temp;
}

/**
 * Remove Meta Tag
 *
 * This function tries to remove all <meta > tags of the string
 *
 * @temp => the string that you want to process
 */
function remove_meta_tag($temp)
{
    $temp = preg_replace('@<meta[^>]*?.*?>@siu', '', $temp);
    return $temp;
}

/**
 * Remove Link Tag
 *
 * This function tries to remove all <link > tags of the string
 *
 * @temp => the string that you want to process
 */
function remove_link_tag($temp)
{
    $temp = preg_replace('@<link\b[^>]*?.*?>@siu', '', $temp);
    return $temp;
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
    $array = [];
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
        foreach ($froms as $from) {
            $array[$from] = $img;
        }
    }
    $html = str_replace_assoc($array, $html);
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
    $array = [];
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
            foreach ($froms as $from) {
                $array[$from] = $img;
            }
        }
    }
    $html = str_replace_assoc($array, $html);
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
    $array = [];
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
        foreach ($froms as $from) {
            $array[$from] = $img;
        }
    }
    $html = str_replace_assoc($array, $html);
    return $html;
}

/**
 * TODO
 *
 * TODO
 */
function __inline_img_helper($src)
{
    $scheme = parse_url($src, PHP_URL_SCHEME);
    if (!in_array($scheme, ['https', 'http'])) {
        return $src;
    }
    $cache = get_cache_file($src, '.b64');
    if (file_exists($cache)) {
        return file_get_contents($cache);
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
        if (isset($data['headers']['content-type'])) {
            $type = $data['headers']['content-type'];
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
            return $img;
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
        $html = str_replace($src, "cid:$hash", $html);
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
            $html = str_replace($src, "cid:$hash", $html);
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
        $html = str_replace($src, "cid:$hash", $html);
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
    $array = [];
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
        foreach ($froms as $from) {
            $array[$from] = __GIF_IMAGE__;
        }
    }
    $html = str_replace_assoc($array, $html);
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
    $array = [];
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
            foreach ($froms as $from) {
                $array[$from] = __GIF_IMAGE__;
            }
        }
    }
    $html = str_replace_assoc($array, $html);
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
    $array = [];
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
        foreach ($froms as $from) {
            $array[$from] = __GIF_IMAGE__;
        }
    }
    $html = str_replace_assoc($array, $html);
    return $html;
}
