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
    if (extension_loaded("pcov") && file_exists("coverage.cov")) {
        pcov\start();
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
    if (extension_loaded("pcov") && file_exists("coverage.cov")) {
        pcov\stop();
        file_put_contents("coverage.cov", serialize(pcov\collect()));
    }
}
