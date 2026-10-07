<?php

/**
 *  ____        _ _    ___  ____  _  _
 * / ___|  __ _| | |_ / _ \/ ___|| || |
 * \___ \ / _` | | __| | | \___ \| || |_
 *  ___) | (_| | | |_| |_| |___) |__   _|
 * |____/ \__,_|_|\__|\___/|____/   |_|
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
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
 * Loading helper function
 *
 * This file contains the needed function used by the unit tests
 */
require_once 'php/lib/unoconv.php';
require_once 'php/lib/import.php';
require_once 'lib/utestlib.php';

/**
 * Main class of this unit test
 */
final class test_unoconv extends TestCase
{
    /**
     * Pdf test helper
     *
     * This function tries to do the test with unoconv2pdf, checks
     * that the input not exists and the output exists to validate
     * the correctness of the function
     *
     * @input => the input file to use in the test
     * @size  => the size in bytes expected for the output
     *
     * Notes:
     *
     * The output is not removed here, it is used as cache by the txt
     * test helper to prevent a second conversion of the same file, and
     * is removed by the caller
     *
     * The size of the output is checked using a delta because the
     * functions always create the output, that is a void file when the
     * conversion fails
     */
    private function test_pdf($input, $size): void
    {
        $output = get_cache_file($input, '.pdf');
        if (file_exists($output)) {
            unlink($output);
        }
        $this->assertFileDoesNotExist($output);

        $buffer = unoconv2pdf($input);
        $this->assertFileExists($output);
        if ($size) {
            $this->assertStringContainsString('PDF document', get_mime($buffer), $input);
        }
        $this->assertEqualsWithDelta($size, strlen($buffer), $size * 0.1, $input);
    }

    /**
     * Txt test helper
     *
     * This function tries to do the test with unoconv2txt, checks
     * that the input not exists and the output exists to validate
     * the correctness of the function
     *
     * @input => the input file to use in the test
     * @size  => the size in bytes expected for the output
     */
    private function test_txt($input, $size): void
    {
        $output = get_cache_file($input, '.txt');
        if (file_exists($output)) {
            unlink($output);
        }
        $this->assertFileDoesNotExist($output);

        $buffer = unoconv2txt($input);
        $this->assertFileExists($output);
        $this->assertEqualsWithDelta($size, strlen($buffer), $size * 0.1, $input);
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
        // Each file defines the size in bytes expected for the pdf and for
        // the txt, the void outputs are the cases where the conversion is
        // not supported
        $files = [
            //'../../utest/files/bigsize.xlsx',
            '../../utest/files/blank.odt' => [6500, 0],
            '../../utest/files/image.pdf' => [97000, 17000],
            '../../utest/files/lorem.html' => [17000, 750],
            '../../utest/files/lorem.odt' => [17000, 750],
            '../../utest/files/lorem.pdf' => [13000, 750],
            '../../utest/files/lorem.png' => [230000, 20000],
            '../../utest/files/multipages.odt' => [23000, 2900],
            '../../utest/files/multipages.pdf' => [460000, 69000],
            '../../utest/files/numbers.bytes' => [260000, 91000],
            '../../utest/files/numbers.csv' => [390000, 47000],
            '../../utest/files/numbers.edi' => [220000, 47000],
            '../../utest/files/numbers.json' => [0, 203000],
            '../../utest/files/numbers.ods' => [230000, 8000],
            '../../utest/files/numbers.xls' => [240000, 8000],
            '../../utest/files/numbers.xlsx' => [240000, 8000],
            '../../utest/files/numbers.xml' => [2400000, 69000],
            '../../utest/files/repeat.pdf' => [6800, 17000],
            '../../utest/files/saltos.sqlite' => [0, 0],
        ];
        foreach ($files as $file => $sizes) {
            $this->test_pdf($file, $sizes[0]);
            $pdf = get_cache_file($file, '.pdf');
            // This file is used to cover the conversion without the pdf cache
            if (basename($file) === 'blank.odt') {
                unlink($pdf);
            }
            $this->test_txt($file, $sizes[1]);
            $this->assertFileExists($pdf);
            unlink($pdf);
        }

        $array = ['value' => ['value' => ['a']]];
        $this->assertSame(__unoconv_node2value($array), 'a');

        $this->assertSame(__unoconv_lines2matrix([
            ['line', -2244, 592, -556, 638],
            ['word', -617, 594, -556, 628, '_'],
            ['word', -617, 594, -556, 628, 'x'],
            ['word', -617, 594, -556, 628, ''],
        ], 1, 1), 3);

        $this->assertSame(__unoconv_lines2matrix([], 1, 1), []);
    }

    #[testdox('ocr functions')]
    /**
     * ocr test
     *
     * This test performs some tests to validate the correctness
     * of the ocr functions
     */
    public function test_ocr(): void
    {
        $file = '../../utest/files/multipages.pdf';
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
            $val = array_values($val);
            // CUT THE PAGES
            $val = __unoconv_substr2d($val, 30, 70, 100, 30, 70, 100);
            $val = implode("\n", $val);
            // CONTINUE
            $ocr[$key] = $val;
        }

        //~ $ocr = implode("\n\n", $ocr);
        //~ print_r($ocr);
    }

    #[testdox('commands functions')]
    /**
     * commands test
     *
     * This test performs some tests to validate the correctness
     * of the commands functions
     */
    public function test_commands(): void
    {
        $this->assertCount(74, __unoconv_list());
        $this->assertSame(__unoconv_convert('', '', ''), null);
        $this->assertSame(__unoconv_pdf2txt('', ''), null);
        $this->assertSame(__unoconv_img2ocr('../../utest/files/lorem.html'), '');
        $this->assertSame(__unoconv_pdf2ocr('../../utest/files/lorem.html'), '');
    }
}
