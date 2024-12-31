<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2025 by Josep Sanz Campderrós
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
 * Test image
 *
 * This test performs some tests to validate the correctness
 * of the barcode, qrcode, captcha and score features
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
require_once 'lib/utestlib.php';
require_once 'php/lib/barcode.php';
require_once 'php/lib/qrcode.php';
require_once 'php/lib/captcha.php';
require_once 'php/lib/score.php';

/**
 * Main class of this unit test
 */
final class test_image extends TestCase
{
    #[testdox('barcode functions')]
    /**
     * barcode test
     *
     * This test performs some tests to validate the correctness
     * of the barcode feature
     */
    public function test_barcode(): void
    {
        $img = __barcode_image('12345', 1, 30, 10, 8, 'C39');
        $this->assertStringContainsString('PNG image data', get_mime($img));
        $gd = @imagecreatefromstring($img);
        $this->assertInstanceOf(GdImage::class, $gd);
        imagedestroy($gd);

        // This case is for the special case when tcpdf doesn't returns valid data
        $this->assertSame(__barcode_image(chr(0), 1, 30, 10, 8, 'C39'), '');

        $json = test_cli_helper('image/barcode', [], '', '', '');
        $this->assertArrayHasKey('error', $json);

        $json = test_cli_helper('image/barcode', [], '', '', 'admin');
        $this->assertArrayHasKey('error', $json);

        $json = test_cli_helper('image/barcode', [
            'msg' => 'nada',
            'format' => 'nada',
        ], '', '', 'admin');
        $this->assertArrayHasKey('error', $json);

        $json = test_cli_helper('image/barcode', [
            'format' => 'png',
        ], '', '', 'admin');
        $this->assertArrayHasKey('error', $json);

        $json = test_cli_helper('image/barcode', [
            'msg' => "\0",
            'format' => 'png',
        ], '', '', 'admin');
        $this->assertArrayHasKey('error', $json);

        $json = test_cli_helper('image/barcode', [
            'msg' => 'nada',
            'format' => 'png',
        ], '', '', 'admin');
        $this->assertStringContainsString('PNG image data', get_mime($json));

        $json = test_cli_helper('image/barcode', [
            'msg' => 'nada',
            'format' => 'json',
        ], '', '', 'admin');
        $this->assertIsArray($json);
        $this->assertSame(count($json), 2);
        $this->assertArrayHasKey('msg', $json);
        $this->assertArrayHasKey('image', $json);

        $file = 'data/logs/phperror.log';
        $this->assertFileDoesNotExist($file);
        $json = test_cli_helper('image/nada', [
            'format' => 'png',
        ], '', '', 'admin');
        $this->assertArrayHasKey('error', $json);
        $this->assertFileExists($file);
        $this->assertTrue(words_exists('unknown action nada', file_get_contents($file)));
        unlink($file);
    }

    #[testdox('qrcode functions')]
    /**
     * qrcode test
     *
     * This test performs some tests to validate the correctness
     * of the qrcode feature
     */
    public function test_qrcode(): void
    {
        $img = __qrcode_image('12345', 6, 10, 'L');
        $this->assertStringContainsString('PNG image data', get_mime($img));
        $gd = @imagecreatefromstring($img);
        $this->assertInstanceOf(GdImage::class, $gd);
        imagedestroy($gd);

        $json = test_cli_helper('image/qrcode', [], '', '', '');
        $this->assertArrayHasKey('error', $json);

        $json = test_cli_helper('image/qrcode', [], '', '', 'admin');
        $this->assertArrayHasKey('error', $json);

        $json = test_cli_helper('image/qrcode', [
            'msg' => 'nada',
            'format' => 'nada',
        ], '', '', 'admin');
        $this->assertArrayHasKey('error', $json);

        $json = test_cli_helper('image/qrcode', [
            'format' => 'png',
        ], '', '', 'admin');
        $this->assertArrayHasKey('error', $json);

        $json = test_cli_helper('image/qrcode', [
            'msg' => 'nada',
            'format' => 'png',
        ], '', '', 'admin');
        $this->assertStringContainsString('PNG image data', get_mime($json));

        $json = test_cli_helper('image/qrcode', [
            'msg' => 'nada',
            'format' => 'json',
        ], '', '', 'admin');
        $this->assertIsArray($json);
        $this->assertSame(count($json), 2);
        $this->assertArrayHasKey('msg', $json);
        $this->assertArrayHasKey('image', $json);

        $json = test_cli_helper('image/qrcode', [
            'msg' => str_repeat('nada', 1000),
            'format' => 'png',
        ], '', '', 'admin');
        $this->assertArrayHasKey('error', $json);

        $file = 'data/logs/phperror.log';
        $this->assertFileDoesNotExist($file);
        $json = test_cli_helper('image/nada', [
            'format' => 'png',
        ], '', '', 'admin');
        $this->assertArrayHasKey('error', $json);
        $this->assertFileExists($file);
        $this->assertTrue(words_exists('unknown action nada', file_get_contents($file)));
        unlink($file);
    }

    #[testdox('captcha functions')]
    /**
     * captcha test
     *
     * This test performs some tests to validate the correctness
     * of the captcha feature
     */
    public function test_captcha(): void
    {
        $img = __captcha_image('12345');
        $this->assertStringContainsString('PNG image data', get_mime($img));
        $gd = @imagecreatefromstring($img);
        $this->assertInstanceOf(GdImage::class, $gd);
        imagedestroy($gd);

        $text = __captcha_make_number(5);
        $this->assertSame(is_numeric($text), true);
        $this->assertSame(strlen($text), 5);

        $text = __captcha_make_math(5);
        $this->assertSame(is_numeric($text), false);
        $this->assertSame(strlen($text), 5);

        $json = test_cli_helper('image/captcha', [], '', '', '');
        $this->assertArrayHasKey('error', $json);

        $json = test_cli_helper('image/captcha', [], '', '', 'admin');
        $this->assertArrayHasKey('error', $json);

        $json = test_cli_helper('image/captcha', [
            'type' => 'nada',
            'format' => 'nada',
        ], '', '', 'admin');
        $this->assertArrayHasKey('error', $json);

        $json = test_cli_helper('image/captcha', [
            'type' => 'number',
            'format' => 'nada',
        ], '', '', 'admin');
        $this->assertArrayHasKey('error', $json);

        $json = test_cli_helper('image/captcha', [
            'format' => 'png',
        ], '', '', 'admin');
        $this->assertArrayHasKey('error', $json);

        $json = test_cli_helper('image/captcha', [
            'type' => 'number',
            'format' => 'png',
        ], '', '', 'admin');
        $this->assertStringContainsString('PNG image data', get_mime($json));

        $json = test_cli_helper('image/captcha', [
            'type' => 'math',
            'format' => 'png',
        ], '', '', 'admin');
        $this->assertStringContainsString('PNG image data', get_mime($json));

        $json = test_cli_helper('image/captcha', [
            'type' => 'number',
            'format' => 'json',
        ], '', '', 'admin');
        $this->assertIsArray($json);
        $this->assertSame(count($json), 2);
        $this->assertArrayHasKey('code', $json);
        $this->assertArrayHasKey('image', $json);

        $file = 'data/logs/phperror.log';
        $this->assertFileDoesNotExist($file);
        $json = test_cli_helper('image/nada', [
            'format' => 'png',
        ], '', '', 'admin');
        $this->assertArrayHasKey('error', $json);
        $this->assertFileExists($file);
        $this->assertTrue(words_exists('unknown action nada', file_get_contents($file)));
        unlink($file);
    }

    #[testdox('score functions')]
    /**
     * score test
     *
     * This test performs some tests to validate the correctness
     * of the score feature
     */
    public function test_score(): void
    {
        $img = __score_image(50, 60, 16, 8);
        $this->assertStringContainsString('PNG image data', get_mime($img));
        $gd = @imagecreatefromstring($img);
        $this->assertInstanceOf(GdImage::class, $gd);
        imagedestroy($gd);

        $json = test_cli_helper('image/score', [], '', '', '');
        $this->assertArrayHasKey('error', $json);

        $json = test_cli_helper('image/score', [], '', '', 'admin');
        $this->assertArrayHasKey('error', $json);

        $json = test_cli_helper('image/score', [
            'pass' => 'nada',
            'format' => 'nada',
        ], '', '', 'admin');
        $this->assertArrayHasKey('error', $json);

        $json = test_cli_helper('image/score', [
            'format' => 'png',
        ], '', '', 'admin');
        $this->assertArrayHasKey('error', $json);

        $json = test_cli_helper('image/score', [
            'pass' => 'nada',
            'format' => 'png',
        ], '', '', 'admin');
        $this->assertStringContainsString('PNG image data', get_mime($json));

        $json = test_cli_helper('image/score', [
            'pass' => 'nada',
            'format' => 'json',
        ], '', '', 'admin');
        $this->assertIsArray($json);
        $this->assertSame(count($json), 3);
        $this->assertArrayHasKey('score', $json);
        $this->assertArrayHasKey('image', $json);
        $this->assertArrayHasKey('valid', $json);

        $file = 'data/logs/phperror.log';
        $this->assertFileDoesNotExist($file);
        $json = test_cli_helper('image/nada', [
            'format' => 'png',
        ], '', '', 'admin');
        $this->assertArrayHasKey('error', $json);
        $this->assertFileExists($file);
        $this->assertTrue(words_exists('unknown action nada', file_get_contents($file)));
        unlink($file);
    }
}
