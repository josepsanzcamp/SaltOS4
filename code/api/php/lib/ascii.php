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
 * Make Table ASCII
 *
 * This table is intended for debug purposes and is able to paint in ascii
 * mode the contents of a matrix
 */

/**
 * Make Table ASCII
 *
 * This table is intended for debug purposes and is able to paint in ascii
 * mode the contents of a matrix
 *
 * @rows    => the contents of the matrix to paint
 * @head    => set to true if you want to use the first row as header
 * @compact => set to true if you want to minify the ascii table
 */
function __ascii_make_table_ascii($array)
{
    // Preparar datos
    if (!is_array($array['rows'])) {
        $array['rows'] = [[$array['rows']]];
        $array['head'] = 0;
    }
    if (!count($array['rows'])) {
        $array['rows'] = [['Data not found']];
        $array['head'] = 0;
    }
    // Inicializar variables locales
    $rows = isset($array['rows']) ? $array['rows'] : [];
    $head = isset($array['head']) ? $array['head'] : 1;
    $compact = isset($array['compact']) ? $array['compact'] : 0;
    // Calcular alineaciones
    $aligns = [];
    foreach ($rows as $row) {
        foreach ($row as $key => $val) {
            if (!isset($aligns[$key])) {
                $aligns[$key] = ['L' => 0, 'R' => 0];
            }
            if (is_numeric($val)) {
                $aligns[$key]['R']++;
            } elseif (substr($val, -1, 1) == '%') {
                $aligns[$key]['R']++;
            } elseif (mb_substr($val, -1, 1) == '€') {
                $aligns[$key]['R']++;
            } else {
                $aligns[$key]['L']++;
            }
        }
    }
    foreach ($aligns as $key => $val) {
        $aligns[$key] = ($val['R'] > $val['L']) ? 'R' : 'L';
    }
    // Calcular medidas
    $widths = [];
    foreach ($rows as $row) {
        foreach ($row as $key => $val) {
            if (!isset($widths[$key])) {
                $widths[$key] = 0;
            }
            $widths[$key] = max(mb_strlen(strval($val)), $widths[$key]);
        }
    }
    // Pintar tabla
    ob_start();
    foreach ($widths as $width) {
        echo '+' . str_repeat('-', $width + ($compact ? 0 : 2));
    }
    echo "+\n";
    foreach ($rows as $index => $row) {
        if ($index == 1 && $head) {
            foreach ($widths as $width) {
                echo '+' . str_repeat('-', $width + ($compact ? 0 : 2));
            }
            echo "+\n";
        }
        foreach ($row as $key => $val) {
            echo '|';
            if ($aligns[$key] == 'R') {
                echo str_repeat(' ', $widths[$key] - mb_strlen(strval($val)));
            }
            echo ($compact ? '' : ' ') . $val . ($compact ? '' : ' ');
            if ($aligns[$key] == 'L') {
                echo str_repeat(' ', $widths[$key] - mb_strlen(strval($val)));
            }
        }
        echo "|\n";
    }
    foreach ($widths as $width) {
        echo '+' . str_repeat('-', $width + ($compact ? 0 : 2));
    }
    echo '+';
    $buffer = ob_get_clean();
    // Bye bye
    return $buffer;
}
