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

/**
 * Loading helper function
 *
 * This file contains the needed function used by the unit tests
 */
require_once "lib/utestlib.php";

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
        $this->assertSame(app2index("invoices"), 1);
        $this->assertSame(app2control("invoices"), 1);
        $this->assertSame(app2version("invoices"), 1);
        $this->assertSame(app2files("invoices"), 1);
        $this->assertSame(app2notes("invoices"), 1);
    }

    #[testdox('app functions')]
    /**
     * app test
     *
     * This test performs some tests to validate the correctness
     * of the app functions
     */
    public function test_app(): void
    {
        $json = test_web_helper("app", null, "", "");
        $this->assertArrayHasKey("error", $json);

        $json = test_web_helper("app/nada", "", "", "");
        $this->assertArrayHasKey("error", $json);

        $json = test_web_helper("app/customers/nada", "", "", "");
        $this->assertArrayHasKey("error", $json);

        $json = test_web_helper("app/customers", "", "", "");
        $this->assertArrayHasKey("error", $json);

        $json = test_web_helper("app/login", "", "", "");
        $this->assertArrayHasKey("layout", $json);

        $json2 = test_web_helper("authtoken", [
            "user" => "admin",
            "pass" => "admin",
        ], "", "");
        $this->assertSame($json2["status"], "ok");
        $this->assertSame(count($json2), 4);
        $this->assertArrayHasKey("token", $json2);

        $json = test_web_helper("app/customers", "", $json2["token"], "");
        $this->assertArrayHasKey("layout", $json);

        $json = test_web_helper("app/customers/widget/plot1", "", $json2["token"], "");
        $this->assertArrayHasKey("data", $json);

        test_external_exec("php/apps1.php", "phperror.log", "nada(nada) not found");

        if (file_exists("apps/nada2/xml/app.xml")) {
            unlink("apps/nada2/xml/app.xml");
        }
        if (file_exists("apps/nada2/xml")) {
            rmdir("apps/nada2/xml");
        }
        if (file_exists("apps/nada2")) {
            rmdir("apps/nada2");
        }

        mkdir("apps/nada2/xml", 0777, true);
        file_put_contents("apps/nada2/xml/app.xml", "<root></root>");

        $json = test_web_helper("app/nada2", "", "", "");
        $this->assertArrayHasKey("error", $json);

        file_put_contents("apps/nada2/xml/app.xml", "<root><nada3></nada3><nada4></nada4></root>");

        $json = test_web_helper("app/nada2", "", "", "");
        $this->assertArrayHasKey("error", $json);

        if (file_exists("apps/nada2/xml/app.xml")) {
            unlink("apps/nada2/xml/app.xml");
        }
        if (file_exists("apps/nada2/xml")) {
            rmdir("apps/nada2/xml");
        }
        if (file_exists("apps/nada2")) {
            rmdir("apps/nada2");
        }
    }

    #[testdox('list functions')]
    /**
     * list test
     *
     * This test performs some tests to validate the correctness
     * of the list functions
     */
    public function test_list(): void
    {
        if (file_exists("apps/nada2/xml/list.xml")) {
            unlink("apps/nada2/xml/list.xml");
        }
        if (file_exists("apps/nada2/xml")) {
            rmdir("apps/nada2/xml");
        }
        if (file_exists("apps/nada2")) {
            rmdir("apps/nada2");
        }

        mkdir("apps/nada2/xml", 0777, true);
        file_put_contents("apps/nada2/xml/list.xml", "<root></root>");

        $json = test_web_helper("list/nada2", "", "", "");
        $this->assertArrayHasKey("error", $json);

        file_put_contents("apps/nada2/xml/list.xml", "<root><nada3></nada3><nada4></nada4></root>");

        $json = test_web_helper("list/nada2", "", "", "");
        $this->assertArrayHasKey("error", $json);

        if (file_exists("apps/nada2/xml/list.xml")) {
            unlink("apps/nada2/xml/list.xml");
        }
        if (file_exists("apps/nada2/xml")) {
            rmdir("apps/nada2/xml");
        }
        if (file_exists("apps/nada2")) {
            rmdir("apps/nada2");
        }
    }
}
