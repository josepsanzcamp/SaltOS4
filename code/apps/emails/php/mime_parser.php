<?php

/**
 *  ____        _ _    ___  ____    _  _    _
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / |
 * \___ \ / _` | | __| | | \___ \  | || |_ | |
 *  ___) | (_| | | |_| |_| |___) | |__   _|| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)_|
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

/**
 * This class replaces the legacy phpclasses.org implementations
 * `mime_parser` and `rfc822_addresses` with PHP's built-in `mailparse`
 * extension. The goal is to keep the output structure compatible with
 * the old classes (`Headers`, `DecodedHeaders`, `ExtractedAddresses`,
 * `Body`, `BodyLength`, and `Parts`) while leveraging `mailparse` for
 * parsing and RFC822 address extraction. It accepts input either as a
 * whole message in memory (`Data`) or via a stream wrapper (`File`),
 * builds a recursive part tree using `mailparse_msg_get_structure()`,
 * and (optionally) extracts decoded bodies for non-multipart parts.
 */
final class mime_parser_class
{
    /** Compatibility with the legacy class: if 0, it won't populate Body/BodyLength */
    public int $decode_bodies = 1;

    /**
     * @param  array $input Must be an array with 'File' or 'Data'
     * @param  array &$decoded Output compatible array (a root node with Parts)
     * @return bool
     */
    public function Decode($input, array &$decoded): bool
    {
        // 1) Build $raw ALWAYS (if 'File' is provided, read and decompress via stream wrapper)
        if (isset($input['File'])) {
            $raw = file_get_contents($input['File']);
            if ($raw === false) {
                throw new RuntimeException("No se pudo leer {$input['File']}");
            }
        } else {
            $raw = $input['Data'];
        }
        unset($input); // Release references to the large string and the array

        // 2) Parse from memory (as if it were always 'Data')
        $h = mailparse_msg_create();
        overload_error_handler('mailparse_msg_parse');
        mailparse_msg_parse($h, $raw);
        restore_error_handler();

        // 3) Structure and tree building
        $struct = mailparse_msg_get_structure($h) ?: [];
        $decoded = [];
        if ($struct) {
            $decoded[] = $this->buildNode($h, $struct[0], $struct, $raw, (bool)$this->decode_bodies);
        }
        unset($raw);                 // no longer needed

        // 4) Cleanup
        mailparse_msg_free($h);

        return true;
    }

    /**
     * @param resource $msg   mailparse message handle
     * @param string   $partId
     * @param array    $struct  cached full structure
     * @param string   $raw     full message in memory
     * @param bool     $decodeBodies
     */
    private function buildNode($msg, string $partId, array $struct, string $raw, bool $decodeBodies): array
    {
        overload_error_handler('input is not rfc822 compliant');
        $p    = mailparse_msg_get_part($msg, $partId);
        restore_error_handler();
        overload_error_handler('input is not rfc822 compliant');
        $meta = mailparse_msg_get_part_data($p) ?: [];
        restore_error_handler();

        $node = [];

        // 1) Raw normalized headers
        $headers = [];
        if (!empty($meta['headers']) && is_array($meta['headers'])) {
            foreach ($meta['headers'] as $k => $v) {
                if (is_string($v)) {
                    $headers[strtolower($k) . ':'] = $v;
                }
            }
        }
        $node['Headers'] = $headers;

        // 2) DecodedHeaders (decode only if it seems MIME-encoded)
        $decodedHeaders = [];
        foreach ($headers as $k => $v) {
            $decodedHeaders[$k] = [[['Value' => mb_decode_mimeheader($v)]]];
        }
        $node['DecodedHeaders'] = $decodedHeaders;

        // 3) ExtractedAddresses
        $addrKeys = [
            'from:',
            'to:',
            'cc:',
            'bcc:',
            'return-path:',
            'reply-to:',
            'disposition-notification-to:',
        ];
        $extracted = [];
        foreach ($addrKeys as $k) {
            $rawVal = $headers[$k] ?? null;
            overload_error_handler('input is not rfc822 compliant');
            $list = $rawVal ? (mailparse_rfc822_parse_addresses($rawVal) ?: []) : [];
            restore_error_handler();
            $arr = [];
            foreach ($list as $a) {
                $email = strtolower($a['address'] ?? '');
                $disp  = mb_decode_mimeheader($a['display'] ?? '');
                $name  = ($disp && strcasecmp($disp, $email) !== 0) ? $disp : '';
                if ($email !== '') {
                    $arr[] = ['name' => $name, 'address' => $email];
                }
            }
            $extracted[$k] = $arr;
        }
        $node['ExtractedAddresses'] = $extracted;

        // 4) FileName
        if (!empty($meta['disposition-filename'])) {
            $node['FileName'] = mb_decode_mimeheader($meta['disposition-filename']);
        } elseif (!empty($meta['content-name'])) {
            $node['FileName'] = mb_decode_mimeheader($meta['content-name']);
        }

        // 5) Body / BodyLength (like the original class: string for non-multipart)
        $ctype = strtolower($meta['content-type'] ?? '');
        $isMultipart = str_starts_with($ctype, 'multipart/');

        if ($decodeBodies && !$isMultipart) {
            // extract from the full in-memory string
            overload_error_handler('mailparse_msg_extract_part');
            // @phpstan-ignore argument.type,function.void
            $node['Body'] = (string)mailparse_msg_extract_part($p, $raw, null);
            restore_error_handler();
            $node['BodyLength'] = strlen($node['Body']);
        }

        // 6) Direct children (using cached structure)
        $children = [];
        $prefix = $partId . '.';
        $dotsParent = substr_count($partId, '.');
        foreach ($struct as $cid) {
            if (str_starts_with($cid, $prefix) && substr_count($cid, '.') === $dotsParent + 1) {
                $children[] = $this->buildNode($msg, $cid, $struct, $raw, $decodeBodies);
            }
        }
        if ($children) {
            $node['Parts'] = $children;
            $children = null;
        }

        return $node;
    }
}
