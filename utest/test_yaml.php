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
// phpcs:disable Generic.Files.LineLength

/**
 * Test YAML
 *
 * This test performs some tests to validate the correctness of the
 * api/php/autoload/yaml.php polyfill against the real yaml PECL
 * extension: it calls the yaml_* functions (native, since this
 * machine has the real extension) and the polyfill's own
 * __yaml_*_helper functions side by side, on the same sample files,
 * and asserts each pair returns the same thing.
 *
 * Calling the *_helper functions directly (instead of the plain
 * yaml_* names) is what lets this run on a machine that has the
 * native extension installed: yaml.php only defines the plain
 * yaml_* names when the extension is missing, so on a machine that
 * does have it, those names are already the native functions and
 * can't be used to reach the polyfill's own code.
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
final class test_yaml extends TestCase
{
    #[testdox('YAML functions')]
    /**
     * YAML test
     *
     * This function performs some tests to validate the correctness
     * of the api/php/autoload/yaml.php polyfill by comparing its
     * output against the real yaml extension, function by function,
     * over a batch of sample YAML files
     */
    public function test_yaml(): void
    {
        $this->assertTrue(extension_loaded('yaml'));

        $files = glob('apps/*/xml/*.yaml');
        $this->assertNotEmpty($files);

        require_once 'lib/yaml/vendor/autoload.php';

        foreach ($files as $file) {
            // yaml_parse_file: native vs polyfill helper
            $native = yaml_parse_file($file);
            $polyfill = __yaml_parse_file_helper($file);
            $this->assertSame($native, $polyfill, $file);

            // yaml_emit: the native extension (libyaml, block style) and
            // Symfony (flow style) serialize the same data as different,
            // both valid, YAML text, and nothing in production reads that
            // text itself (grep finds no other caller of yaml_emit*), so
            // comparing the emitted strings verbatim would fail on a
            // cosmetic difference that doesn't matter. What matters is
            // that a file emitted on one side is readable by the other,
            // e.g. moving a host from having the native extension to not
            // having it (or back) must not corrupt already-written data
            $emittedNative = yaml_emit($native);
            $emittedPolyfill = __yaml_emit_helper($polyfill);

            $this->assertSame($native, yaml_parse($emittedNative), $file);
            $this->assertSame($native, __yaml_parse_helper($emittedNative), $file);
            $this->assertSame($polyfill, yaml_parse($emittedPolyfill), $file);
            $this->assertSame($polyfill, __yaml_parse_helper($emittedPolyfill), $file);

            // same cross-read check, through yaml_emit_file/yaml_parse_file
            $cache1 = get_cache_file(['native' => $file]);
            yaml_emit_file($cache1, $native);

            $cache2 = get_cache_file(['polyfill' => $file]);
            __yaml_emit_file_helper($cache2, $polyfill);

            $this->assertSame($native, yaml_parse_file($cache1), $file);
            $this->assertSame($native, __yaml_parse_file_helper($cache1), $file);
            $this->assertSame($polyfill, yaml_parse_file($cache2), $file);
            $this->assertSame($polyfill, __yaml_parse_file_helper($cache2), $file);
        }
    }
}
