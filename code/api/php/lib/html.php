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
 * Inline Img Tag
 *
 * This function tries to convert all imgs to an inline imgs
 *
 * @temp => the string that you want to process
 */
function inline_img_tag($temp)
{
    $tags = __get_imgs_tags($temp);
    foreach ($tags as $tag) {
        $attrs = __explode_attr($tag);
        $src = '';
        foreach ($attrs as $key => $val) {
            if (strtolower($key) == 'src') {
                $src = $val;
                break;
            }
        }
        $img = 'data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=';
        $scheme = parse_url($src, PHP_URL_SCHEME);
        if (in_array($scheme, ['data', 'cid'])) {
            continue;
        }
        if (in_array($scheme, ['https', 'http'])) {
            $cache = get_cache_file($src, '.tmp');
            if (!file_exists($cache)) {
                $data = __url_get_contents($src);
                $valid = false;
                foreach ($data['headers'] as $key => $val) {
                    $key = substr(strtolower($key), 0, 12);
                    $valid = in_array($key, ['http/1.1 200', 'http/2.0 200']);
                    if ($valid) {
                        break;
                    }
                }
                if ($valid) {
                    if (isset($data['headers']['content-type'])) {
                        $type = $data['headers']['content-type'];
                    } else {
                        $type = saltos_content_type_from_string($data['body']);
                    }
                    if (saltos_content_type0($type) == 'image') {
                        $img = mime_inline($type, $data['body']);
                    }
                }
                file_put_contents($cache, $img);
                chmod_protected($cache, 0666);
            } else {
                $img = file_get_contents($cache);
            }
        }
        $temp = str_replace($src, $img, $temp);
    }
    return $temp;
}

/**
 * TODO
 *
 * TODO
 */
function __get_imgs_tags($html)
{
    $imgs = [];
    $pos = stripos($html, '<img ');
    $len = strlen($html);
    while ($pos !== false) {
        $pos2 = $pos;
        for (;;) {
            $pos2 = strpos($html, '>', $pos2 + 1);
            $img = substr($html, $pos, $pos2 - $pos);
            $chars = count_chars($img);
            if (($chars[ord('"')] & 0x1) != 0) {
                continue;
            }
            if (($chars[ord("'")] & 0x1) != 0) {
                continue;
            }
            break;
        };
        $imgs[] = $img;
        $pos = stripos($html, '<img ', $pos2);
    }
    return $imgs;
}

/**
 * TODO
 *
 * TODO
 */
function __explode_attr($html)
{
    $result = [];
    $len = strlen($html);
    $pos0 = strpos($html, '=');
    while ($pos0 !== false) {
        for ($i = $pos0 - 1; $i >= 0; $i--) {
            if ($html[$i] != ' ') {
                break;
            }
        }
        $pos2 = $i;
        for ($i = $pos2; $i >= 0; $i--) {
            if ($html[$i] == ' ') {
                break;
            }
        }
        $pos1 = $i + 1;
        $pos2++;
        for ($i = $pos0 + 1; $i < $len; $i++) {
            if ($html[$i] != ' ') {
                break;
            }
        }
        $pos3 = $i;
        $next = ' ';
        if ($html[$i] == '"' || $html[$i] == "'") {
            $next = $html[$i];
            $pos3++;
        }
        for ($i = $pos3; $i < $len; $i++) {
            if ($html[$i] == $next) {
                break;
            }
        }
        $pos4 = $i;
        $key = substr($html, $pos1, $pos2 - $pos1);
        $val = substr($html, $pos3, $pos4 - $pos3);
        $result[$key] = $val;
        if ($pos4 + 1 < $len) {
            $pos0 = strpos($html, '=', $pos4 + 1);
        } else {
            break;
        }
    }
    return $result;
}
