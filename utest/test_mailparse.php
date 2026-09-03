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
 * Test mailparse
 *
 * This test performs some tests to validate the correctness of the
 * apps/emails/php/mailparse.php polyfill against the real mailparse
 * PECL extension. It does NOT go through mime_parser_class: it calls
 * the mailparse_* functions (native, since this machine has the real
 * extension) and the polyfill's own __mailparse_*_helper functions
 * side by side, on the same sample emails, and asserts each pair
 * returns the same thing. That keeps this test independent from
 * mime_parser_class.php's own transformation logic, which is free to
 * change without this test needing to track it.
 *
 * Calling the *_helper functions directly (instead of the plain
 * mailparse_* names) is what lets this run on a machine that has the
 * native extension installed: mailparse.php only defines the plain
 * mailparse_* names when the extension is missing, so on a machine
 * that does have it, those names are already the native functions and
 * can't be used to reach the polyfill's own code. See the header
 * comment of apps/emails/php/mailparse.php for details.
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
require_once 'apps/emails/php/mailparse.php';

/**
 * Main class of this unit test
 */
final class test_mailparse extends TestCase
{
    #[testdox('mailparse polyfill functions')]
    /**
     * Mailparse test
     *
     * This function performs some tests to validate the correctness
     * of the apps/emails/php/mailparse.php polyfill by comparing its
     * output against the real mailparse extension, function by
     * function, over a batch of sample emails
     */
    public function test_mailparse(): void
    {
        $this->assertTrue(extension_loaded('mailparse'));

        $files = glob('data/inbox/1/*.eml.gz');
        $this->assertNotEmpty($files);

        $addrKeys = [
            'from:',
            'to:',
            'cc:',
            'bcc:',
            'return-path:',
            'reply-to:',
            'disposition-notification-to:',
        ];

        foreach ($files as $file) {
            $raw = file_get_contents('compress.zlib://' . $file);
            $this->assertNotFalse($raw, $file);

            // mailparse_msg_create/parse/get_structure: native vs polyfill
            $hNative = mailparse_msg_create();
            mailparse_msg_parse($hNative, $raw);
            $structNative = mailparse_msg_get_structure($hNative);

            $hPolyfill = __mailparse_msg_create_helper();
            __mailparse_msg_parse_helper($hPolyfill, $raw);
            $structPolyfill = __mailparse_msg_get_structure_helper($hPolyfill);

            $this->assertSame($structNative, $structPolyfill, $file);

            foreach ($structNative as $partId) {
                $label = "$file part $partId";

                // mailparse_msg_get_part + mailparse_msg_get_part_data
                $pNative = mailparse_msg_get_part($hNative, $partId);
                $pPolyfill = __mailparse_msg_get_part_helper($hPolyfill, $partId);

                // the polyfill only covers the subset of keys that
                // mime_parser_class.php actually reads (see the header
                // comment of apps/emails/php/mailparse.php): the native
                // extension's array is richer (starting-pos, charset,
                // transfer-encoding...) by design, and it omits
                // disposition-filename/content-name entirely rather than
                // returning them empty when absent, which mime_parser_class.php
                // treats the same via !empty() either way, so scope and
                // default the comparison to that documented subset
                $metaNative = $this->scoped_part_data(mailparse_msg_get_part_data($pNative));
                $metaPolyfill = $this->scoped_part_data(__mailparse_msg_get_part_data_helper($pPolyfill));

                $this->assertSame($metaNative, $metaPolyfill, $label);

                // mailparse_msg_extract_part: on a multipart container,
                // the native extension returns the raw substring of the
                // whole message between this part's boundaries (envelope
                // included), while the polyfill's getContent() has no
                // notion of "this container's own raw text" and returns
                // ''; mime_parser_class.php only ever calls this for
                // non-multipart parts, so scope the comparison the same
                // way instead of asserting on a case nothing consumes
                if (!str_starts_with($metaNative['content-type'], 'multipart/')) {
                    $bodyNative = mailparse_msg_extract_part($pNative, $raw, null);
                    $bodyPolyfill = __mailparse_msg_extract_part_helper($pPolyfill, $raw, null);

                    $this->assertSame($bodyNative, $bodyPolyfill, $label);
                }

                // mailparse_rfc822_parse_addresses, for every address header
                // present on this part
                foreach ($addrKeys as $k) {
                    $rawVal = $metaNative['headers'][$k] ?? null;
                    if (!is_string($rawVal) || $rawVal === '') {
                        continue;
                    }
                    $addrNative = mailparse_rfc822_parse_addresses($rawVal);
                    $addrPolyfill = __mailparse_rfc822_parse_addresses_helper($rawVal);
                    $this->assertSame($addrNative, $addrPolyfill, "$label header $k");
                }
            }

            mailparse_msg_free($hNative);
            __mailparse_msg_free_helper($hPolyfill);
        }
    }

    /**
     * Scoped part data
     *
     * Reduces a mailparse_msg_get_part_data()/__mailparse_msg_get_part_data_helper()
     * result to the keys mime_parser_class.php actually reads, defaulting the
     * optional ones to '' so an omitted key (native, when absent) and an
     * empty-string key (polyfill, always present) compare equal, matching how
     * mime_parser_class.php treats them via !empty() either way
     *
     * @data => raw result from either side
     */
    private function scoped_part_data(array $data): array
    {
        return [
            'headers' => $data['headers'] ?? [],
            'content-type' => $data['content-type'] ?? '',
            'disposition-filename' => $data['disposition-filename'] ?? '',
            'content-name' => $data['content-name'] ?? '',
        ];
    }
}
