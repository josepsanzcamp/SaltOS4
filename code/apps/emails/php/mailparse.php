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

// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration
// phpcs:disable Squiz.Classes.ValidClassName

/**
 * Mailparse helper module
 *
 * This file provides the subset of functions of the `mailparse` PECL
 * extension that mime_parser_class.php actually uses, intended to be
 * used by setups that can not install this extension. Backed by the
 * pure PHP composer package zbateson/mail-mime-parser.
 *
 * Scope: only mailparse_msg_create, mailparse_msg_parse,
 * mailparse_msg_get_structure, mailparse_msg_get_part,
 * mailparse_msg_get_part_data, mailparse_msg_extract_part,
 * mailparse_msg_free and mailparse_rfc822_parse_addresses are provided,
 * because those are the only ones consumed by mime_parser_class.php.
 * The 'headers'/'content-type'/'disposition-filename'/'content-name'
 * keys of mailparse_msg_get_part_data() are the only ones populated
 * (the real extension returns a richer array).
 *
 * Note: unlike the real extension, the Body extracted here is
 * charset-normalized to UTF-8 by the underlying library (it only
 * reverses the Content-Transfer-Encoding, mailparse leaves the
 * original charset bytes as-is). Review any code that re-converts
 * Body's charset by hand before relying on this polyfill in production.
 *
 * Two known, deliberately-not-chased differences, verified harmless
 * against a large real-inbox sample compared via utest/test_mailparse.php:
 *
 * - Trailing whitespace on a header's value: the library trims it during
 *   its own header parsing (Parser/HeaderParserService.php), before it is
 *   exposed through any public method, so it can not be recovered here.
 *   The real extension keeps it. Purely cosmetic (e.g. a Subject ending
 *   in one extra space), never observed to affect parsing logic.
 *
 * - Two adjacent RFC 2047 encoded-words in a display name (a name split
 *   across encoded-words for line-length reasons): the real extension's
 *   mb_decode_mimeheader() collapses the whitespace between them, which
 *   can glue two words together (eg. "SerniRibó"). The library decodes
 *   each encoded-word independently and keeps the whitespace between
 *   them (eg. "Serni Ribó"), which reads as the more correct behaviour
 *   and is what this polyfill produces.
 *
 * Every mailparse_* function below is a thin delegate (only defined when
 * the real extension is missing) to a same-named __mailparse_*_helper()
 * that is always defined. That split exists so utest/test_mailparse.php
 * can call the helper directly and exercise this polyfill's actual code
 * on machines that do have the native extension installed, where the
 * plain mailparse_* names are already taken by the extension and can't
 * be used to reach this file's code.
 */

if (!class_exists('__mailparse_handle_helper')) {
    /**
     * Internal handle used by the functions below to keep the parsed
     * message and its dot-notation ("1", "1.1", "1.1.1"...) part map
     * together, emulating the resource returned by mailparse_msg_create().
     *
     * Not part of the mailparse extension's API: naming follows the
     * project's convention for internal/helper symbols (leading "__",
     * trailing "_helper"), same as __getmail_head_helper() elsewhere in
     * this app, so it reads at a glance as "not a drop-in replacement".
     */
    final class __mailparse_handle_helper
    {
        public $message = null;
        /** @var array<string, \ZBateson\MailMimeParser\Message\IMessagePart> */
        public array $parts = [];
    }
}

if (!function_exists('__mailparse_index_parts_helper')) {
    /**
     * Mailparse Index Parts Helper
     *
     * Walks the part tree returned by zbateson/mail-mime-parser and
     * assigns it mailparse-style dot-notation ids ("1", "1.1", "1.2",
     * "1.1.1"...), matching what mime_parser_class.php expects to find
     * in the array returned by mailparse_msg_get_structure().
     *
     * A message/rfc822 part (a forwarded email carried as an attachment)
     * does not expose its inner structure through getChildParts(): the
     * library treats it as an opaque leaf whose content happens to be a
     * full embedded message. mailparse_msg_get_structure() from the real
     * extension does recurse into it, so we parse that embedded content
     * separately here and splice the result in as this part's single
     * child, matching the native behaviour.
     *
     * @part => current part being indexed
     * @id   => dot-notation id to assign to this part
     * @out  => output map, id => part, built by reference
     */
    function __mailparse_index_parts_helper($part, string $id, array &$out): void
    {
        $out[$id] = $part;
        $children = method_exists($part, 'getChildParts') ? $part->getChildParts() : [];
        if ($children) {
            $i = 1;
            foreach ($children as $child) {
                __mailparse_index_parts_helper($child, $id . '.' . $i, $out);
                $i++;
            }
            return;
        }
        $ctype = strtolower((string)$part->getHeaderValue('Content-Type', ''));
        if (str_starts_with($ctype, 'message/rfc822') && method_exists($part, 'getContent')) {
            $embeddedRaw = (string)$part->getContent();
            if ($embeddedRaw !== '') {
                require_once 'apps/emails/lib/mailmimeparser/vendor/autoload.php';
                $embedded = (new \ZBateson\MailMimeParser\MailMimeParser())->parse($embeddedRaw, false);
                __mailparse_index_parts_helper($embedded, $id . '.1', $out);
            }
        }
    }
}

if (!function_exists('__mailparse_unfold_helper')) {
    /**
     * Mailparse Unfold Helper
     *
     * getRawValue() de la libreria conserva el folding (RFC 5322) de la
     * cabecera tal cual aparece en el mensaje original (con el CRLF/LF
     * antes del espacio de continuacion). La extension mailparse real
     * desdobla la cabecera (unfolding), quitando esos saltos de linea y
     * dejandola en una sola linea. Replicamos ese mismo comportamiento
     * aqui para que el resultado sea comparable.
     *
     * @value => valor crudo de la cabecera, potencialmente con folding
     */
    function __mailparse_unfold_helper(string $value): string
    {
        return preg_replace('/\r\n[ \t]|\r[ \t]|\n[ \t]/', ' ', $value);
    }
}

if (!function_exists('__mailparse_encode_display_helper')) {
    /**
     * Mailparse Encode Display Helper
     *
     * getName() de la libreria ya devuelve el nombre mostrado con el
     * RFC 2047 (encoded-word) decodificado a UTF-8. La extension mailparse
     * real, en cambio, devuelve ese display name tal cual aparece en el
     * mensaje (todavia codificado si el remitente uso encoded-words), ya
     * que quien llama a mailparse_rfc822_parse_addresses() en SaltOS
     * (mime_parser_class) pasa siempre ese valor por mb_decode_mimeheader()
     * despues. Para mantener el mismo contrato aqui, si el nombre ya
     * decodificado contiene bytes no-ASCII lo volvemos a codificar como
     * encoded-word: mb_decode_mimeheader() no toca el texto ASCII plano
     * pero corrompe UTF-8 crudo que no venga envuelto en "=?...?=".
     *
     * Se reutiliza igual para disposition-filename/content-name, que
     * getHeaderParameter() tambien devuelve ya decodificados y que
     * mime_parser_class.php tambien pasa por mb_decode_mimeheader().
     *
     * @name => nombre ya decodificado devuelto por AddressPart::getName()
     *          o por getHeaderParameter()
     */
    function __mailparse_encode_display_helper(string $name): string
    {
        if ($name === '' || mb_check_encoding($name, 'ASCII')) {
            return $name;
        }
        return mb_encode_mimeheader($name, 'UTF-8', 'B');
    }
}

if (!function_exists('__mailparse_msg_create_helper')) {
    /**
     * Mailparse Msg Create Helper
     *
     * @path => adjust to wherever your lib/ layout puts this package,
     * following the same convention as lib/yaml/vendor/autoload.php
     */
    function __mailparse_msg_create_helper()
    {
        require_once 'apps/emails/lib/mailmimeparser/vendor/autoload.php';
        return new __mailparse_handle_helper();
    }
}

if (!function_exists('mailparse_msg_create')) {
    /**
     * Mailparse Msg Create
     */
    function mailparse_msg_create()
    {
        return __mailparse_msg_create_helper();
    }
}

if (!function_exists('__mailparse_msg_parse_helper')) {
    /**
     * Mailparse Msg Parse Helper
     *
     * @msg  => handle returned by mailparse_msg_create()
     * @data => the full raw message
     */
    function __mailparse_msg_parse_helper($msg, string $data): bool
    {
        require_once 'apps/emails/lib/mailmimeparser/vendor/autoload.php';
        $parser = new \ZBateson\MailMimeParser\MailMimeParser();
        $msg->message = $parser->parse($data, false);
        __mailparse_index_parts_helper($msg->message, '1', $msg->parts);
        return true;
    }
}

if (!function_exists('mailparse_msg_parse')) {
    /**
     * Mailparse Msg Parse
     */
    function mailparse_msg_parse($msg, string $data): bool
    {
        return __mailparse_msg_parse_helper($msg, $data);
    }
}

if (!function_exists('__mailparse_msg_get_structure_helper')) {
    /**
     * Mailparse Msg Get Structure Helper
     *
     * @msg => handle previously passed to mailparse_msg_parse()
     */
    function __mailparse_msg_get_structure_helper($msg): array
    {
        // array_keys() castea de vuelta a int las claves puramente
        // numericas como "1" (comportamiento normal de PHP con arrays),
        // asi que hay que forzar el string explicitamente aqui.
        return array_map('strval', array_keys($msg->parts));
    }
}

if (!function_exists('mailparse_msg_get_structure')) {
    /**
     * Mailparse Msg Get Structure
     */
    function mailparse_msg_get_structure($msg): array
    {
        return __mailparse_msg_get_structure_helper($msg);
    }
}

if (!function_exists('__mailparse_msg_get_part_helper')) {
    /**
     * Mailparse Msg Get Part Helper
     *
     * @msg    => handle previously passed to mailparse_msg_parse()
     * @partId => one of the ids returned by mailparse_msg_get_structure()
     */
    function __mailparse_msg_get_part_helper($msg, string $partId)
    {
        return $msg->parts[$partId] ?? null;
    }
}

if (!function_exists('mailparse_msg_get_part')) {
    /**
     * Mailparse Msg Get Part
     */
    function mailparse_msg_get_part($msg, string $partId)
    {
        return __mailparse_msg_get_part_helper($msg, $partId);
    }
}

if (!function_exists('__mailparse_msg_get_part_data_helper')) {
    /**
     * Mailparse Msg Get Part Data Helper
     *
     * Only fills the keys that mime_parser_class.php reads: headers,
     * content-type, disposition-filename and content-name.
     *
     * @part => part returned by mailparse_msg_get_part()
     */
    function __mailparse_msg_get_part_data_helper($part): array
    {
        if ($part === null) {
            return [];
        }
        $headers = [];
        foreach ($part->getAllHeaders() as $h) {
            $name = strtolower($h->getName());
            $value = __mailparse_unfold_helper($h->getRawValue());
            if (!array_key_exists($name, $headers)) {
                $headers[$name] = $value;
            } elseif (is_array($headers[$name])) {
                $headers[$name][] = $value;
            } else {
                $headers[$name] = [$headers[$name], $value];
            }
        }
        return [
            'headers' => $headers,
            'content-type' => strtolower((string)$part->getHeaderValue('Content-Type', '')),
            'disposition-filename' => __mailparse_encode_display_helper(
                (string)$part->getHeaderParameter('Content-Disposition', 'filename', '')
            ),
            'content-name' => __mailparse_encode_display_helper(
                (string)$part->getHeaderParameter('Content-Type', 'name', '')
            ),
        ];
    }
}

if (!function_exists('mailparse_msg_get_part_data')) {
    /**
     * Mailparse Msg Get Part Data
     */
    function mailparse_msg_get_part_data($part): array
    {
        return __mailparse_msg_get_part_data_helper($part);
    }
}

if (!function_exists('__mailparse_msg_extract_part_helper')) {
    /**
     * Mailparse Msg Extract Part Helper
     *
     * @part     => part returned by mailparse_msg_get_part()
     * @data     => full raw message (unused here, the library already
     *              keeps its own reference to the content while parsing)
     * @callback => decoding callback (unused, always fully decoded)
     */
    function __mailparse_msg_extract_part_helper($part, string $data, $callback = null): string
    {
        if ($part === null) {
            return '';
        }
        return (string)$part->getContent();
    }
}

if (!function_exists('mailparse_msg_extract_part')) {
    /**
     * Mailparse Msg Extract Part
     */
    function mailparse_msg_extract_part($part, string $data, $callback = null): string
    {
        return __mailparse_msg_extract_part_helper($part, $data, $callback);
    }
}

if (!function_exists('__mailparse_msg_free_helper')) {
    /**
     * Mailparse Msg Free Helper
     *
     * @msg => handle previously returned by mailparse_msg_create()
     */
    function __mailparse_msg_free_helper($msg): bool
    {
        if ($msg instanceof __mailparse_handle_helper) {
            $msg->message = null;
            $msg->parts = [];
        }
        return true;
    }
}

if (!function_exists('mailparse_msg_free')) {
    /**
     * Mailparse Msg Free
     */
    function mailparse_msg_free($msg): bool
    {
        return __mailparse_msg_free_helper($msg);
    }
}

if (!function_exists('__mailparse_rfc822_parse_addresses_helper')) {
    /**
     * Mailparse Rfc822 Parse Addresses Helper
     *
     * Parses a raw address header value (e.g. the content of a "To:"
     * header) into a list of ['display' => ..., 'address' => ...].
     *
     * Builds a throwaway one-header message under a made-up header name
     * so the value is run through the library's own RFC-822 address
     * consumer, then forces it to be read as an AddressHeader via
     * getHeaderAs() regardless of whether that header name is one of
     * the library's pre-registered address headers.
     *
     * @addresses => raw header value, e.g. '"Doe, John" <john@example.com>'
     */
    function __mailparse_rfc822_parse_addresses_helper(string $addresses): array
    {
        require_once 'apps/emails/lib/mailmimeparser/vendor/autoload.php';
        $tmp = \ZBateson\MailMimeParser\Message::from('X-Addr: ' . $addresses . "\r\n\r\n", false);
        $header = $tmp->getHeaderAs('X-Addr', \ZBateson\MailMimeParser\Header\AddressHeader::class);
        $out = [];
        if ($header instanceof \ZBateson\MailMimeParser\Header\AddressHeader) {
            foreach ($header->getAddresses() as $a) {
                $name = (string)$a->getName();
                if ($name === '') {
                    // legacy "addr@host (Display Name)" form: there is no
                    // proper display-name before the address, but the real
                    // extension picks up the trailing parenthesized comment
                    // as if it were one, so we do the same here
                    $comments = $a->getComments();
                    if ($comments) {
                        $name = $comments[0]->getComment();
                    }
                }
                $out[] = [
                    'display' => __mailparse_encode_display_helper($name),
                    'address' => (string)$a->getEmail(),
                ];
            }
        }
        return $out;
    }
}

if (!function_exists('mailparse_rfc822_parse_addresses')) {
    /**
     * Mailparse Rfc822 Parse Addresses
     */
    function mailparse_rfc822_parse_addresses(string $addresses): array
    {
        return __mailparse_rfc822_parse_addresses_helper($addresses);
    }
}
