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
 * Test html
 *
 * This test performs some tests to validate the correctness
 * of the html functions
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
require_once 'apps/emails/php/html.php';

/**
 * Main class of this unit test
 */
final class test_html extends TestCase
{
    #[testdox('html functions')]
    /**
     * html test
     *
     * This test performs some tests to validate the correctness
     * of the html functions
     */
    public function test_html(): void
    {
        $this->assertSame(remove_script_tag('<script></script>'), '');
        $this->assertSame(remove_style_tag('<style></style>'), '');
        $this->assertSame(remove_comment_tag('<!-- nada -->'), '');
        $this->assertSame(remove_meta_tag('<meta nada>'), '');
        $this->assertSame(remove_link_tag('<link nada>'), '');

        $src = 'https://127.0.0.1/favicon.ico';
        $cache = get_cache_file($src, '.b64');
        if (file_exists($cache)) {
            unlink($cache);
        }

        $this->assertFileDoesNotExist($cache);
        $this->assertStringContainsString(
            mime_inline('file/b64', basename($cache)),
            inline_img_tag("<img src='$src'>")
        );
        $this->assertFileExists($cache);

        $this->assertTrue(words_exists(
            'data image base64',
            file_get_contents($cache)
        ));

        $this->assertTrue(words_exists(
            file_get_contents($cache),
            fix_file_b64(inline_img_tag("<img src='$src'>"))
        ));

        $this->assertTrue(words_exists(
            'data file/b64 base64',
            inline_img_tag("<img src='$src'>")
        ));

        $this->assertSame(inline_img_tag(''), '');

        $this->assertTrue(words_exists(
            'data file/b64 base64',
            inline_img_tag("<img src=\"$src\">")
        ));

        $this->assertTrue(words_exists(
            'data file/b64 base64',
            inline_img_tag("<img src=\" $src \">")
        ));

        $this->assertTrue(words_exists(
            'data file/b64 base64',
            inline_img_tag("<img src=\"\n$src\n\">")
        ));

        $this->assertSame(inline_img_style(''), '');

        $this->assertTrue(words_exists(
            'data file/b64 base64',
            inline_img_style("<div style='background:url($src)'>")
        ));

        $this->assertTrue(words_exists(
            'data file/b64 base64',
            inline_img_style("<div style='background:url(\"$src\")'>")
        ));

        $this->assertTrue(words_exists(
            'data file/b64 base64',
            inline_img_style("<div style=\"background:url('$src')\">")
        ));

        $this->assertSame(inline_img_background(''), '');

        $this->assertTrue(words_exists(
            'data file/b64 base64',
            inline_img_background("<table background='$src'>")
        ));

        $this->assertTrue(words_exists(
            'data file/b64 base64',
            inline_img_background("<table background=\"$src\">")
        ));

        $src = 'https://127.0.0.1/nada';
        $this->assertTrue(words_exists(
            'data image base64',
            inline_img_tag("<img src='$src'>")
        ));

        $src = 'data:image/gif;base64,nada';
        $this->assertTrue(words_exists(
            'data image base64 nada',
            inline_img_tag("<img src='$src'>")
        ));

        $this->assertTrue(words_exists(
            'data image base64 nada',
            inline_img_style("<div style=\"background:url('$src')\">")
        ));

        $this->assertTrue(words_exists(
            'data image base64 nada',
            inline_img_background("<table background=\"$src\">")
        ));

        $this->assertSame(extract_img_tag(''), ['', []]);

        $src = __GIF_IMAGE__;
        $html = "<img src=\"$src\">";
        [$html, $files] = extract_img_tag($html);
        $hash = md5(mime_extract(__GIF_IMAGE__)['data']);
        $this->assertStringContainsString("cid:$hash", $html);
        $this->assertSame($files[$hash]['type'], 'image/gif');

        $src = 'cid:nada';
        $html = "<img src=\"$src\">";
        [$html2, $files] = extract_img_tag($html);
        $this->assertSame($html2, $html);
        $this->assertCount(0, $files);

        $this->assertSame(extract_img_style(''), ['', []]);

        $src = __GIF_IMAGE__;
        $html = "<div style='background:url($src)'>";
        [$html, $files] = extract_img_style($html);
        $hash = md5(mime_extract(__GIF_IMAGE__)['data']);
        $this->assertStringContainsString("cid:$hash", $html);
        $this->assertSame($files[$hash]['type'], 'image/gif');

        $src = __GIF_IMAGE__;
        $html = "<div style='background:url(\"$src\")'>";
        [$html, $files] = extract_img_style($html);
        $hash = md5(mime_extract(__GIF_IMAGE__)['data']);
        $this->assertStringContainsString("cid:$hash", $html);
        $this->assertSame($files[$hash]['type'], 'image/gif');

        $src = __GIF_IMAGE__;
        $html = "<div style=\"background:url('$src')\">";
        [$html, $files] = extract_img_style($html);
        $hash = md5(mime_extract(__GIF_IMAGE__)['data']);
        $this->assertStringContainsString("cid:$hash", $html);
        $this->assertSame($files[$hash]['type'], 'image/gif');

        $src = 'cid:nada';
        $html = "<div style='background:url($src)'>";
        [$html2, $files] = extract_img_style($html);
        $this->assertSame($html2, $html);
        $this->assertCount(0, $files);

        $this->assertSame(extract_img_background(''), ['', []]);

        $src = __GIF_IMAGE__;
        $html = "<table background=\"$src\">";
        [$html, $files] = extract_img_background($html);
        $hash = md5(mime_extract(__GIF_IMAGE__)['data']);
        $this->assertStringContainsString("cid:$hash", $html);
        $this->assertSame($files[$hash]['type'], 'image/gif');

        $src = 'cid:nada';
        $html = "<table background=\"$src\">";
        [$html2, $files] = extract_img_background($html);
        $this->assertSame($html2, $html);
        $this->assertCount(0, $files);

        $this->assertSame(fix_img_tag(''), '');

        $src = 'cid:nada';
        $this->assertTrue(words_exists(
            'data image base64',
            fix_img_tag("<img src='$src'>")
        ));

        $src = __GIF_IMAGE__;
        $this->assertTrue(words_exists(
            'data image base64',
            fix_img_tag("<img src='$src'>")
        ));

        $this->assertSame(fix_img_style(''), '');

        $src = 'cid:nada';
        $this->assertTrue(words_exists(
            'data image base64',
            fix_img_style("<div style='background:url($src)'>")
        ));

        $src = __GIF_IMAGE__;
        $this->assertTrue(words_exists(
            'data image base64',
            fix_img_style("<div style='background:url($src)'>")
        ));

        $src = 'cid:nada';
        $this->assertTrue(words_exists(
            'data image base64',
            fix_img_style("<div style='background:url(\"$src\")'>")
        ));

        $src = 'cid:nada';
        $this->assertTrue(words_exists(
            'data image base64',
            fix_img_style("<div style=\"background:url('$src')\">")
        ));

        $this->assertSame(fix_img_background(''), '');

        $src = 'cid:nada';
        $this->assertTrue(words_exists(
            'data image base64',
            fix_img_background("<table background='$src'>")
        ));

        $src = __GIF_IMAGE__;
        $this->assertTrue(words_exists(
            'data image base64',
            fix_img_background("<table background='$src'>")
        ));

        $src = 'https://127.0.0.1/nada';
        $cache = get_cache_file($src, '.err');
        if (file_exists($cache)) {
            unlink($cache);
        }

        $this->assertFileDoesNotExist($cache);
        $this->assertSame(
            __GIF_IMAGE__,
            __inline_img_helper($src)
        );
        $this->assertFileExists($cache);

        $this->assertSame(remove_meta_tag(''), '');

        $html = '<META name="from" content="Carrefour <noreply@login.carrefour.com>" />';
        $this->assertSame(remove_meta_tag($html), '');

        $this->assertSame(remove_link_tag(''), '');

        $html = '<LINK name="from" content="Carrefour <noreply@login.carrefour.com>" />';
        $this->assertSame(remove_link_tag($html), '');
    }
}
