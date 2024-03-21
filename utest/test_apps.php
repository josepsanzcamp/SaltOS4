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
 * Test apps
 *
 * This test performs some tests to validate the correctness
 * of the apps functions
 */

/**
 * Importing namespaces
 */
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\DependsExternal;

/**
 * Main class of this unit test
 */
final class test_apps extends TestCase
{
    #[testdox('apps functions')]
    /**
     * apps test
     *
     * This test performs some tests to validate the correctness
     * of the apps functions
     */
    public function test_apps(): void
    {
        global $_CONFIG;
        $_CONFIG = eval_attr(xmlfiles2array(detect_config_files("xml/config.xml")));
        db_connect();

        $this->assertSame(app2id("invoices"), 12);
        $this->assertSame(id2app(12), "invoices");
        $this->assertSame(id2app(12), "invoices");
        $this->assertSame(id2table(12), "app_invoices");
        $this->assertSame(app2table("invoices"), "app_invoices");
        $this->assertSame(table2id("app_invoices"), 12);
        $this->assertSame(table2app("app_invoices"), "invoices");
        $this->assertSame(count(id2subtables(12)), 2);
        $this->assertSame(count(app2subtables("invoices")), 2);
        $this->assertSame(count(table2subtables("app_invoices")), 2);
        $this->assertSame(app_exists("invoices"), true);
        $this->assertSame(count(detect_apps_files("xml/dbschema.xml")) > 1, true);
    }
}
