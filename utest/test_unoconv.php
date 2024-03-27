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
 * Test unoconv
 *
 * This test performs some tests to validate the correctness
 * of the unoconv functions
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
final class test_unoconv extends TestCase
{
    /**
     * TODO
     *
     * TODO
     */
    private function test_pdf($input): void
    {
        $output = get_cache_file($input, ".pdf");
        if (file_exists($output)) {
            unlink($output);
        }
        $this->assertFileDoesNotExist($output);

        $buffer = unoconv2pdf($input);
        $this->assertFileExists($output);
        unlink($output);
    }

    private function test_txt($input): void
    {
        $output = get_cache_file($input, ".txt");
        if (file_exists($output)) {
            unlink($output);
        }
        $this->assertFileDoesNotExist($output);

        $buffer = unoconv2txt($input);
        $this->assertFileExists($output);
        unlink($output);
    }

    #[testdox('unoconv functions')]
    /**
     * unoconv test
     *
     * This test performs some tests to validate the correctness
     * of the unoconv functions
     */
    public function test_unoconv(): void
    {
        $files = [
            //~ "../../utest/files/bigsize.xlsx",
            "../../utest/files/image.pdf",
            "../../utest/files/lorem.html",
            "../../utest/files/lorem.odt",
            "../../utest/files/lorem.pdf",
            "../../utest/files/lorem.png",
            //~ "../../utest/files/numbers.bytes",
            "../../utest/files/numbers.csv",
            //~ "../../utest/files/numbers.edi",
            "../../utest/files/numbers.json",
            //~ "../../utest/files/numbers.ods",
            //~ "../../utest/files/numbers.xls",
            //~ "../../utest/files/numbers.xlsx",
            //~ "../../utest/files/numbers.xml",
        ];
        foreach ($files as $file) {
            $this->test_pdf($file);
            $this->test_txt($file);
        }

        $file = "../../utest/files/multipages.pdf";
        $ocr = __unoconv_pdf2ocr($file);

        $ocr = explode("\n\n", $ocr);
        $this->assertSame(count($ocr), 4);

        foreach ($ocr as $key => $val) {
            // REMOVE MARGINS
            $val = __unoconv_remove_margins($val);
            // REMOVE VOID LINES
            $val = explode("\n", $val);
            foreach ($val as $key2 => $val2) {
                if (!trim($val2)) {
                    unset($val[$key2]);
                }
            }
            $val = __unoconv_substr2d($val, 10, 20, 30, 10, 90, 100);
            $val = implode("\n", $val);
            // CONTINUE
            $ocr[$key] = $val;
        }

        //~ $ocr = implode("\n\n", $ocr);
        //~ print_r($ocr);
    }
}
