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
 * PCOV helper module
 *
 * This fie contains useful functions related to the pcov module used to
 * measure the coverage of the unit tests
 */

/**
 * PCOV start
 *
 * This function start the pcov recording to allow the measuring of the
 * coverage in the unit tests.
 */
function pcov_start()
{
    if (extension_loaded('pcov') && file_exists('data/temp/pcov.out')) {
        \pcov\start();
    }
}

/**
 * PCOV stop
 *
 * This function stop the pcov recording that allow the measuring of the
 * coverage in the unit tests and puts in the output file the collected
 * data in a serialized format
 */
function pcov_stop()
{
    if (extension_loaded('pcov') && file_exists('data/temp/pcov.out')) {
        \pcov\stop();
        file_put_contents('data/temp/pcov.out', serialize(\pcov\collect(\pcov\all)));
        chmod_protected('data/temp/pcov.out', 0666);
    }
}
