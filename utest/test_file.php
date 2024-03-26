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
 * Test file
 *
 * This test performs some tests to validate the correctness
 * of the file functions
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
final class test_file extends TestCase
{
    #[testdox('file functions')]
    /**
     * file test
     *
     * This test performs some tests to validate the correctness
     * of the file functions
     */
    public function test_file(): void
    {
        $this->assertSame(get_directory("dirs/tempdir"), getcwd() . "/data/temp/");

        $this->assertSame(get_directory("dirs/tempdir2"), null);

        set_config("dirs/tempdir2", ["#attr" => ["eval" => "true"], "value" => "getcwd().'/data/temp/'"]);
        $this->assertSame(get_directory("dirs/tempdir2"), getcwd() . "/data/temp/");

        $this->assertStringContainsString(getcwd() . "/data/temp/", get_temp_file());
        $this->assertSame(strlen(getcwd() . "/data/temp/") + 32 + 4, strlen(get_temp_file("tmp")));

        $this->assertStringContainsString(getcwd() . "/data/cache/", get_cache_file(""));
        $this->assertSame(strlen(getcwd() . "/data/cache/") + 32 + 4, strlen(get_cache_file([], "tmp")));

        $file1 = get_temp_file();
        file_put_contents($file1, "");
        $file2 = get_temp_file();
        file_put_contents($file2, "");
        sleep(1); // the internally filemtime used have one second of resolution
        $file3 = get_temp_file();
        $this->assertSame(cache_exists($file3, [$file1, $file2]), false);
        file_put_contents($file3, "");
        $this->assertSame(cache_exists($file3, $file1), true);
        $this->assertSame(cache_exists($file1, ["nada"]), false);
        $this->assertSame(cache_exists($file3, [$file1, $file2]), true);
        $this->assertSame(cache_exists($file1, [$file3, $file2]), false);
        unlink($file1);
        unlink($file2);
        unlink($file3);

        $json0 = url_get_contents("127.0.0.1/saltos/code4/api/index.php?checktoken");
        $json = url_get_contents("https://127.0.0.1/saltos/code4/api/index.php?checktoken");
        $this->assertSame($json0, $json);
        $json = json_decode($json, true);
        $this->assertSame($json["status"], "ko");
        $this->assertSame(count($json), 3);
        $this->assertArrayHasKey("reason", $json);

        $this->assertSame(extension("pepe.txt"), "txt");

        $this->assertSame(encode_bad_chars_file("Hola Mundo.txt"), "hola_mundo.txt");

        $this->assertSame(realpath_protected(getcwd() . "/pepe.txt"), getcwd() . "/pepe.txt");

        $this->assertSame(getcwd_protected(), getcwd());
        $oldcwd = getcwd();
        chdir("/");
        $this->assertSame(getcwd_protected(), dirname(get_server("SCRIPT_FILENAME")));
        chdir($oldcwd);

        $this->assertSame(is_array(glob_protected("*")), true);
        $this->assertSame(count(glob_protected("*")) > 0, true);
        $this->assertSame(is_array(glob_protected("nada")), true);
        $this->assertSame(count(glob_protected("nada")) == 0, true);

        $file = get_temp_file();
        file_put_contents($file, "");
        $this->assertSame(chmod_protected($file, 0666), true);
        $this->assertSame(chmod_protected($file, 0666), false);
        unlink($file);

        $file = get_directory("dirs/cachedir") .
            ob_passthru("ls -l data/cache | grep www-data | tr ' ' '\n' | tail -1");
        $this->assertSame(chmod_protected($file, 0664), false);

        $file = get_temp_file();
        file_put_contents($file, "");
        $this->assertSame(strlen($file) + 1 + 32, strlen(file_with_hash($file)));
        unlink($file);

        $errno = 0;
        $errstr = "";
        $fd = fsockopen_protected("127.0.0.1", 80, $errno, $errstr, null);
        $this->assertSame(is_resource($fd), true);

        $buffer = __url_get_contents("https://127.0.0.1nada/saltos/code4/api/index.php?checktoken");
        $this->assertSame($buffer, ["", [], []]);

        $buffer = __url_get_contents("nada://127.0.0.1/saltos/code4/api/index.php?checktoken");
        $this->assertSame($buffer, ["", [], []]);

        $buffer = __url_get_contents("https://127.0.0.1/saltos/code4/api/index.php?checktoken", [
            "method" => "",
        ]);
        $this->assertSame($buffer, ["", [], []]);

        $buffer = __url_get_contents("https://127.0.0.1/saltos/code4/api/index.php?checktoken", [
            "method" => "head",
        ]);
        $this->assertSame($buffer, ["", [], []]);

        $buffer = __url_get_contents("https://127.0.0.1/saltos/code4/api/index.php?checktoken", [
            "cookies" => ["nada" => "nada"],
            "method" => "get",
            "values" => ["nada" => "nada"],
            "referer" => "https://127.0.0.1/saltos/code4/api/index.php",
            "headers" => ["nada" => "nada"],
            "body" => "nada",
        ]);
        $this->assertSame(is_array($buffer), true);
        $this->assertSame(strlen($buffer["body"]) > 0, true);
    }
}
