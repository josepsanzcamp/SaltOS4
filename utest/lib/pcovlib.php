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
 * PCOV helper function
 *
 * This file contains the functions used by the web and cli unit tests
 * to get the coverage
 */

/**
 * Importing namespaces
 */
use PHPUnit\SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\SebastianBergmann\CodeCoverage\Test\TestStatus\TestStatus;

/**
 * TODO
 *
 * TODO
 */
function test_pcov_start(): void
{
    file_put_contents("coverage.cov", "");
    chmod("coverage.cov", 0666);
}

/**
 * TODO
 *
 * TODO
 */
function test_pcov_stop(): void
{
    $collected = unserialize(file_get_contents("coverage.cov"));
    unlink("coverage.cov");

    $coverage = RawCodeCoverageData::fromXdebugWithoutPathCoverage($collected);
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    $method = "unknown::unknown";
    if (isset($backtrace[2]) && isset($backtrace[2]["function"]) && isset($backtrace[2]["class"])) {
        $method = $backtrace[2]["class"] . "::" . $backtrace[2]["function"];
    }
    CodeCoverage::instance()->codeCoverage()->append(
        $coverage,
        $method,
        true,
        TestStatus::unknown(),
    );
}
