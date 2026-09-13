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
            'from',
            'to',
            'cc',
            'bcc',
            'return-path',
            'reply-to',
            'disposition-notification-to',
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
                    $addrNative = array_map(
                        [$this, 'scoped_address'],
                        mailparse_rfc822_parse_addresses($rawVal)
                    );
                    $addrPolyfill = array_map(
                        [$this, 'scoped_address'],
                        __mailparse_rfc822_parse_addresses_helper($rawVal)
                    );
                    $this->assertSame($addrNative, $addrPolyfill, "$label header $k");
                }
            }

            mailparse_msg_free($hNative);
            __mailparse_msg_free_helper($hPolyfill);
        }
    }

    #[testdox('mailparse polyfill edge cases')]
    /**
     * Mailparse edge cases test
     *
     * Covers branches that the real-inbox sample in test_mailparse()
     * never happens to exercise: a message/rfc822 part (a forwarded
     * email carried as an attachment), a header repeated more than
     * once, a non-ASCII display/filename value, a null part, and the
     * legacy "addr@host (Display Name)" address form
     */
    public function test_mailparse_edge_cases(): void
    {
        // A message/rfc822 part must be recursed into, splicing the
        // embedded message in as this part's single child, matching
        // the native extension's own recursion into it
        $embedded = "From: inner@example.com\r\n"
            . "To: innerto@example.com\r\n"
            . "Subject: Inner\r\n"
            . "Content-Type: text/plain\r\n\r\n"
            . "Inner body\r\n";
        $boundary = 'BOUNDARY123';
        $raw = "From: outer@example.com\r\n"
            . "To: outerto@example.com\r\n"
            . "Subject: Outer with attachment\r\n"
            . "X-Custom-Header: first\r\n"
            . "X-Custom-Header: second\r\n"
            . "X-Custom-Header: third\r\n"
            . "MIME-Version: 1.0\r\n"
            . "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n\r\n"
            . "--$boundary\r\n"
            . "Content-Type: text/plain\r\n\r\n"
            . "Outer body\r\n"
            . "--$boundary\r\n"
            . "Content-Type: message/rfc822\r\n\r\n"
            . $embedded
            . "--$boundary--\r\n";

        $hNative = mailparse_msg_create();
        mailparse_msg_parse($hNative, $raw);
        $structNative = mailparse_msg_get_structure($hNative);

        $hPolyfill = __mailparse_msg_create_helper();
        __mailparse_msg_parse_helper($hPolyfill, $raw);
        $structPolyfill = __mailparse_msg_get_structure_helper($hPolyfill);

        $this->assertSame($structNative, $structPolyfill);
        // "1.2" is the message/rfc822 attachment, "1.2.1" its embedded body
        $this->assertContains('1.2.1', $structPolyfill);

        $pPolyfill = __mailparse_msg_get_part_helper($hPolyfill, '1.2.1');
        $dataPolyfill = __mailparse_msg_get_part_data_helper($pPolyfill);
        $this->assertSame('text/plain', $dataPolyfill['content-type']);
        $this->assertSame('Inner', $dataPolyfill['headers']['subject']);

        // A header repeated 3 times must be collected into an array,
        // both the native extension and the polyfill agree on this
        $pNative = mailparse_msg_get_part($hNative, '1');
        $pPolyfill1 = __mailparse_msg_get_part_helper($hPolyfill, '1');
        $metaNative = mailparse_msg_get_part_data($pNative);
        $metaPolyfill = __mailparse_msg_get_part_data_helper($pPolyfill1);
        $this->assertSame(
            $metaNative['headers']['x-custom-header'],
            $metaPolyfill['headers']['x-custom-header']
        );
        $this->assertSame(['first', 'second', 'third'], $metaPolyfill['headers']['x-custom-header']);

        mailparse_msg_free($hNative);
        __mailparse_msg_free_helper($hPolyfill);

        // A non-ASCII display/filename value must be re-encoded as an
        // RFC 2047 encoded-word, matching what mb_decode_mimeheader()
        // downstream in mime_parser_class.php expects to unwrap
        $this->assertSame('', __mailparse_encode_display_helper(''));
        $this->assertSame('Plain ASCII', __mailparse_encode_display_helper('Plain ASCII'));
        $encoded = __mailparse_encode_display_helper('Ñoño');
        $this->assertStringStartsWith('=?UTF-8?B?', $encoded);
        $this->assertSame('Ñoño', mb_decode_mimeheader($encoded));

        // A null part (id not found) must return the same empty value
        // as the native extension on both accessors
        $this->assertSame([], __mailparse_msg_get_part_data_helper(null));
        $this->assertSame('', __mailparse_msg_extract_part_helper(null, ''));

        // The legacy "addr@host (Display Name)" form: there is no real
        // display name before the address, but both the native
        // extension and this polyfill pick up the trailing
        // parenthesized comment as if it were one
        $addrRaw = 'legacy@example.com (Legacy Display Name)';
        $addrNative = mailparse_rfc822_parse_addresses($addrRaw);
        $addrPolyfill = __mailparse_rfc822_parse_addresses_helper($addrRaw);
        $this->assertSame($addrNative[0]['display'], $addrPolyfill[0]['display']);
        $this->assertSame('Legacy Display Name', $addrPolyfill[0]['display']);
        $this->assertSame('legacy@example.com', $addrPolyfill[0]['address']);
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

    /**
     * Scoped address
     *
     * Reduces a mailparse_rfc822_parse_addresses()/
     * __mailparse_rfc822_parse_addresses_helper() entry to what
     * mime_parser_class.php actually derives from it. Two differences
     * are normalized away here rather than chased in the polyfill:
     *
     * - 'is_group': never read by mime_parser_class.php, so it's just
     *   dropped from both sides.
     * - 'display' when there is no real display name: the native
     *   extension echoes the address itself as 'display' in that case,
     *   while the underlying library (and this polyfill) leaves it as
     *   '''. mime_parser_class.php already collapses both to '' via its
     *   own `strcasecmp($disp, $email) !== 0` check, so that's exactly
     *   what's replicated here.
     *
     * @entry => one entry as returned by either side
     */
    private function scoped_address(array $entry): array
    {
        $email = strtolower($entry['address'] ?? '');
        $display = $entry['display'] ?? '';
        return [
            'display' => (strcasecmp($display, $email) === 0) ? '' : $display,
            'address' => $entry['address'] ?? '',
        ];
    }
}
