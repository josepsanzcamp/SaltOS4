<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
 */

declare(strict_types=1);

/**
 * License and legal information helpers for SaltOS framework.
 *
 * Provides structured access to copyright, license metadata,
 * third-party libraries and full legal text.
 */

/**
 * Returns the copyright notice.
 *
 * @return string
 */
function get_copyright()
{
    return 'Copyright (c) 2007-2026 Josep Sanz Campderrós';
}

/**
 * Returns license metadata.
 *
 * @return array{
 *     license: string,
 *     license_id: string
 * }
 */
function get_license()
{
    return [
        'license' => 'MIT License',
        'license_id' => 'MIT',
    ];
}

/**
 * Returns the SaltOS ASCII header and project metadata.
 *
 * @return string[]
 */
function get_header()
{
    $header = [
        ' ____        _ _    ___  ____    _  _    ___  ',
        '/ ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \ ',
        '\___ \ / _` | | __| | | \___ \  | || |_| | | |',
        ' ___) | (_| | | |_| |_| |___) | |__   _| |_| |',
        '|____/ \__,_|_|\__|\___/|____/     |_|(_)___/ ',
        '',
        'SaltOS: Framework to develop Rich Internet Applications',
        'Copyright (c) 2007-2026 Josep Sanz Campderrós',
        'SPDX-License-Identifier: MIT',
        'Licensed under the MIT License.',
        'See the LICENSE file in the project root for full license information.',
    ];
    return $header;
}

/**
 * Returns third-party libraries information grouped by area
 * and basic statistics.
 *
 * @return array{
 *     libraries: array<string,array>,
 *     stats: array<string,int>
 * }
 */
function get_libraries()
{
    $libraries = [
        'api' => '../api/lib/licenses.yaml',
        'web' => '../web/lib/licenses.yaml',
        'apps' => '../apps/*/lib/licenses.yaml',
    ];
    $stats = [];
    foreach ($libraries as $key => $val) {
        $files = glob($val);
        $libraries[$key] = [];
        foreach ($files as $file) {
            $items = yaml_parse_file($file);
            foreach ($items as $key2 => $val2) {
                $dir = dirname($file) . '/' . $val2['id'];
                if (file_exists($dir . '/VERSION')) {
                    $items[$key2]['version'] = trim(file_get_contents($dir . '/VERSION'));
                } else {
                    $items[$key2]['version'] = null;
                }
            }
            $libraries[$key] = array_merge($libraries[$key], $items);
        }
        $stats[$key] = count($libraries[$key]);
        usort($libraries[$key], function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });
    }
    $stats['total'] = array_sum($stats);
    return [
        'libraries' => $libraries,
        'stats' => $stats,
    ];
}

/**
 * Returns the full MIT license legal text.
 *
 * @return string[]
 */
function get_legal()
{
    $header = [
        'The MIT License (MIT)',
        '',
        'Copyright (c) 2007-2026 Josep Sanz Campderrós',
        '',
        'Permission is hereby granted, free of charge, to any person obtaining a copy',
        'of this software and associated documentation files (the "Software"), to deal',
        'in the Software without restriction, including without limitation the rights',
        'to use, copy, modify, merge, publish, distribute, sublicense, and/or sell',
        'copies of the Software, and to permit persons to whom the Software is',
        'furnished to do so, subject to the following conditions:',
        '',
        'The above copyright notice and this permission notice shall be included in all',
        'copies or substantial portions of the Software.',
        '',
        'THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR',
        'IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,',
        'FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE',
        'AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER',
        'LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,',
        'OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE',
        'SOFTWARE.',
    ];
    return $header;
}
