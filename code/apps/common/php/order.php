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
 * Helper file for the order feature
 */

/**
 * Get Order Fields
 *
 * Get Orderable Fields from XML Definition, to do it, parses a given XML file
 * and extracts the list of field names that are marked as orderable.
 *
 * @xmlfile => Path to the XML file containing the layout definition.
 *
 * Returns an array of field names that have the 'order' attribute enabled.
 */
function get_order_fields($xmlfile)
{
    $array = xmlfile2array($xmlfile);
    $array = xpath_search_first_value('list[id=cache]/layout/row/table/header', $array);
    $fields = [];
    foreach ($array as $key => $val) {
        $val = join_attr_value($val);
        if (isset($val['order']) && eval_bool($val['order'])) {
            $fields[] = $key;
        }
    }
    return $fields;
}
