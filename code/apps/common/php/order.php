<?php

/**
 *  ____        _ _    ___  ____    _  _    _
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / |
 * \___ \ / _` | | __| | | \___ \  | || |_ | |
 *  ___) | (_| | | |_| |_| |___) | |__   _|| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)_|
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
 */

declare(strict_types=1);

/**
 * Helper file for the order feature
 */

/**
 * Get Order Fields
 *
 * Get Orderable Fields from the XML Definition, to do it, parses a given
 * XML file and extracts the list of field names that are marked as orderable.
 *
 * @app => app to search the xml file that contains the layout definition.
 *
 * Returns an array of field names that have the 'order' attribute enabled.
 */
function get_order_fields($app)
{
    $file = detect_app_file($app);
    $array = xmlfile2array($file);
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
