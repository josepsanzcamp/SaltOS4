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

// phpcs:disable PSR1.Classes.ClassDeclaration
// phpcs:disable Squiz.Classes.ValidClassName
// phpcs:disable PSR1.Methods.CamelCapsMethodName
// phpcs:disable PSR1.Files.SideEffects

/**
 * Test datetime
 *
 * This test performs some tests to validate the correctness
 * of the datetime functions
 */

/**
 * Importing namespaces
 */
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Depends;

/**
 * Main class of this unit test
 */
final class test_datetime extends TestCase
{
    #[testdox('datetime functions')]
    /**
     * datetime test
     *
     * This test performs some tests to validate the correctness
     * of the datetime functions
     */
    public function test_datetime(): void
    {
        $this->assertSame(strlen(current_date()), 10);
        $this->assertSame(strlen(current_time()), 8);
        $this->assertSame(strlen(current_datetime()), 19);
        $this->assertSame(strlen(current_decimals()), 4);
        $this->assertSame(strlen(current_datetime_decimals()), 24);
        $this->assertSame(dateval(0), "0000-00-00");
        $this->assertSame(timeval(0), "00:00:00");
        $this->assertSame(datetimeval(0), "0000-00-00 00:00:00");
        $this->assertSame(dateval("9999-99-99"), "9999-12-31");
        $this->assertSame(dateval("99-99-9999"), "9999-12-31");
        $this->assertSame(timeval("99:99:99"), "24:00:00");
        $this->assertSame(datetimeval("9999-99-99 99:99:99"), "9999-12-31 23:59:59");
        $this->assertSame(datetimeval("99-99-9999 99:99:99"), "9999-12-31 23:59:59");
        $this->assertSame(datetimeval("9999-99-99T99:99:99"), "9999-12-31 23:59:59");
        $this->assertSame(datetimeval("99-99-9999T99:99:99"), "9999-12-31 23:59:59");
        $this->assertSame(__time2secs("23:59:59"), 86400 - 1);
        $this->assertSame(__time2secs("24:00:00"), 86400);
        $this->assertSame(__secs2time(86400 - 1), "23:59:59");
        $this->assertSame(__secs2time(86400), "24:00:00");
        $this->assertSame(strlen(current_dow()), 1);
    }
}
