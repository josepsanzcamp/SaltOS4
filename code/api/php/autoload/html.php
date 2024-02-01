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
 * TODO
 *
 * TODO
 */
function href_replace($temp)
{
    return $temp;
}
