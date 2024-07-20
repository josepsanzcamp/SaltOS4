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
    $temp = preg_replace("@<script[^>]*?.*?</script>@siu", "", $temp);
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
    $temp = preg_replace("@<style[^>]*?.*?</style>@siu", "", $temp);
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
    $temp = preg_replace("@<!--[^>]*?.*?-->@siu", "", $temp);
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
    $temp = preg_replace("@<meta[^>]*?.*?>@siu", "", $temp);
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
    $last = 0;
    for(;;) {
        $pos1 = stripos($temp, "<img", $last);
        if ($pos1 === false) {
            break;
        }
        $pos2 = strpos($temp, ">", $pos1);
        if ($pos2 === false) {
            break;
        }
        $img1 = substr($temp, $pos1, $pos2 - $pos1 + 1);
        // This code load the img tag and replaces the src by the inline data
        $doc = new DOMDocument();
        $doc->loadHTML($img1, LIBXML_COMPACT | LIBXML_NOERROR);
        $tags = $doc->getElementsByTagName('img');
        foreach ($tags as $tag) {
            $src = $tag->getAttribute("src");
            $scheme = parse_url($src, PHP_URL_SCHEME);
            if (in_array($scheme, ["https", "http"])) {
                $cache = get_cache_file($src);
                if (!file_exists($cache)) {
                    $data = __url_get_contents($src);
                    file_put_contents($cache, serialize($data));
                    chmod_protected($cache, 0666);
                } else {
                    $data = unserialize(file_get_contents($cache));
                }
                $valid = false;
                foreach ($data["headers"] as $key => $val) {
                    $valid = in_array(strtolower($key), ["http/1.1 200 ok", "http/2.0 200"]);
                    if ($valid) {
                        break;
                    }
                }
                if ($valid) {
                    $type = $data["headers"]["content-type"];
                    $data = mime_inline($type, $data["body"]);
                } else {
                    // defeult image of 1x1 pixel transparent gif
                    $data = "data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=";
                }
                $tag->setAttribute("src", $data);
            }
        }
        $img2 = $doc->saveHTML($tag);
        // The new img tag is ready to be replaced
        $temp = str_replace($img1, $img2, $temp);
        $last = $pos2;
    }
    return $temp;
}
