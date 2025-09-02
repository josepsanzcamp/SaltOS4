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

/**
 * pop3_class (replacement using PHP imap extension in POP3 mode)
 * Drop-in for old PHPClasses pop3_class.
 *
 * Requirements: ext-imap enabled.
 */
class pop3_class
{
    // --- Public API-compatible properties ---
    public $hostname = '';
    public $port = 110;      // default POP3
    public $tls = 0;         // 1 => /tls
    public $ssl = 0;         // (opcional) 1 => /ssl si lo quisieras usar en vez de tls
    public $novalidate = 0;  // 1 => /novalidate-cert

    // --- Internal state ---
    private $mailboxString = '';
    private $stream = null;
    private $user = '';
    private $pass = '';
    private $opened = false;

    // message buffering to emulate OpenMessage/GetMessage
    private $currentMsgIndex = null;
    private $currentMsgData = '';
    private $currentMsgPtr = 0;

    // tracks if any delete was requested (to expunge on close)
    private $hasDeletes = false;

    // --- Helpers ---
    private function lastError(): string
    {
        $e = imap_last_error();
        imap_errors(); // clear stack
        return $e ? (string)$e : 'Unknown IMAP/POP3 error';
    }

    private function buildMailboxString(): string
    {
        $host = $this->hostname ?: 'localhost';
        $port = (int)$this->port ?: 110;

        $flags = ['/pop3'];
        if ($this->tls) {
            $flags[] = '/tls';
        }
        if ($this->ssl) {
            $flags[] = '/ssl';
        }
        if ($this->novalidate) {
            $flags[] = '/novalidate-cert';
        }

        // POP3 no tiene carpetas; el nombre tras } es irrelevante, usamos INBOX por compatibilidad
        return sprintf('{%s:%d%s}INBOX', $host, $port, implode('', $flags));
    }

    // --- Public API ---

    /**
     * Emula el Open() original: aquí solo prepara el mailbox string.
     * La conexión real se hace en Login(), como en la clase antigua.
     * @return string '' si ok, o mensaje de error
     */
    public function Open(): string
    {
        if (!$this->hostname) {
            return 'POP3 host not set';
        }
        $this->mailboxString = $this->buildMailboxString();
        return '';
    }

    /**
     * Abre la conexión autentificada (imap_open en modo POP3).
     * @return string '' si ok, o mensaje de error
     */
    public function Login(string $user, string $pass): string
    {
        $this->user = $user;
        $this->pass = $pass;

        // Timeouts razonables
        imap_timeout(IMAP_OPENTIMEOUT, 20);
        imap_timeout(IMAP_READTIMEOUT, 60);
        imap_timeout(IMAP_WRITETIMEOUT, 60);

        $params = 0; // opciones
        $n_retries = 1;

        overload_error_handler('imap_open');
        $stream = imap_open($this->mailboxString, $this->user, $this->pass, $params, $n_retries);
        restore_error_handler();
        if ($stream === false) {
            return $this->lastError();
        }
        $this->stream = $stream;
        $this->opened = true;

        // This line prevent the follow phperror.log notice:
        // PHP Request Shutdown: SECURITY PROBLEM: insecure server advertised AUTH=PLAIN
        imap_errors();

        // Continue
        return '';
    }

    /**
     * Lista de mensajes: tamaños (uidls=0) o UIDL (uidls=1).
     * Devuelve array indexado 1..N o string de error.
     */
    public function ListMessages(string $mailbox = '', int $uidls = 0)
    {
        if (!$this->opened || !$this->stream) {
            return 'Not connected';
        }
        overload_error_handler('imap_num_msg');
        $num = imap_num_msg($this->stream);
        restore_error_handler();
        if ($num === false) {
            return $this->lastError();
        }
        if ($num < 1) {
            return []; // sin mensajes
        }

        if ($uidls === 0) {
            // Tamaños vía overview
            overload_error_handler('imap_fetch_overview');
            $ov = imap_fetch_overview($this->stream, "1:$num", 0);
            restore_error_handler();
            if ($ov === false) {
                return $this->lastError();
            }
            $sizes = [];
            foreach ($ov as $o) {
                // $o->msgno es 1..N
                $sizes[$o->msgno] = isset($o->size) ? (int)$o->size : 0;
            }
            return $sizes;
        } else {
            // UIDL reales; fallback a md5(header) si no hay UID
            $uidlsArr = [];
            for ($i = 1; $i <= $num; $i++) {
                overload_error_handler('imap_uid');
                $uid = imap_uid($this->stream, $i);
                restore_error_handler();
                // @phpstan-ignore identical.alwaysFalse
                if ($uid === false || $uid === 0 || $uid === '') {
                    overload_error_handler('imap_fetchheader');
                    $hdr = imap_fetchheader($this->stream, $i, 0);
                    restore_error_handler();
                    if ($hdr === false) {
                        return $this->lastError();
                    }
                    $uid = md5($hdr);
                }
                $uidlsArr[$i] = (string)$uid;
            }
            return $uidlsArr;
        }
    }

    /**
     * Prepara un buffer con el mensaje completo.
     * $length se ignora (mantenemos signatura).
     * @return string '' si ok, o mensaje de error
     */
    public function OpenMessage(int $index, int $length = -1): string
    {
        if (!$this->opened || !$this->stream) {
            return 'Not connected';
        }

        // Cabeceras completas (sin flags)
        overload_error_handler('imap_fetchheader');
        $hdr = imap_fetchheader($this->stream, $index, 0);
        restore_error_handler();
        if ($hdr === false) {
            return $this->lastError();
        }

        // Cuerpo completo (sin flags)
        overload_error_handler('imap_body');
        $body = imap_body($this->stream, $index, 0);
        restore_error_handler();
        if ($body === false) {
            return $this->lastError();
        }

        // --- Normaliza el "seam" header/body sin recortar contenido ---
        // Cuenta CRLF al final del header (0,1,2)
        $t = 0;
        $end4 = substr($hdr, -4);
        if ($end4 === "\r\n\r\n") {
            $t = 2;
        } elseif (substr($hdr, -2) === "\r\n") {
            $t = 1;
        }

        // Cuenta CRLF al inicio del body (0,1,2)
        $b = 0;
        $start4 = substr($body, 0, 4);
        if ($start4 === "\r\n\r\n") {
            $b = 2;
        } elseif (substr($body, 0, 2) === "\r\n") {
            $b = 1;
        }

        // Añade solo los CRLF necesarios para alcanzar 2 en total
        $need = 2 - ($t + $b);
        if ($need > 0) {
            $hdr .= str_repeat("\r\n", $need);
        }

        $this->currentMsgIndex = $index;
        $this->currentMsgData  = $hdr . $body;
        $this->currentMsgPtr   = 0;
        return '';
    }

    /**
     * Emula lectura por trozos desde el buffer cargado por OpenMessage().
     * @param  int    $max  Máximo de bytes a leer en esta llamada
     * @param  string &$out Bloque leído
     * @param  int    &$eof 1 cuando no queda nada más por leer, 0 en caso contrario
     * @return string '' si ok, o mensaje de error
     */
    public function GetMessage(int $max, string &$out, int &$eof): string
    {
        if ($this->currentMsgIndex === null) {
            $out = '';
            $eof = 1;
            return 'No message opened';
        }
        $remaining = strlen($this->currentMsgData) - $this->currentMsgPtr;
        if ($remaining <= 0) {
            $out = '';
            $eof = 1;
            return '';
        }
        $readLen = ($max > 0) ? min($max, $remaining) : $remaining;
        $out = substr($this->currentMsgData, $this->currentMsgPtr, $readLen);
        $this->currentMsgPtr += $readLen;
        $eof = ($this->currentMsgPtr >= strlen($this->currentMsgData)) ? 1 : 0;
        return '';
    }

    /**
     * Marca un mensaje para borrar (se expurga en Close()).
     * @return string '' si ok, o mensaje de error
     */
    public function DeleteMessage(int $index): string
    {
        if (!$this->opened || !$this->stream) {
            return 'Not connected';
        }
        overload_error_handler('imap_delete');
        $ok = imap_delete($this->stream, (string)$index, 0); // flag \Deleted
        restore_error_handler();
        // @phpstan-ignore identical.alwaysFalse
        if ($ok === false) {
            return $this->lastError();
        }
        $this->hasDeletes = true;
        return '';
    }

    /**
     * Cierra la sesión; si hubo deletes, hace EXPUNGE.
     * @return string '' si ok, o mensaje de error
     */
    public function Close(): string
    {
        if ($this->stream) {
            $flags = $this->hasDeletes ? CL_EXPUNGE : 0;
            overload_error_handler('imap_close');
            $ok = imap_close($this->stream, $flags);
            restore_error_handler();
            // @phpstan-ignore identical.alwaysFalse
            if ($ok === false) {
                // Aun si falla, intentamos liberar
                $err = $this->lastError();
                $this->stream = null;
                $this->opened = false;
                return $err;
            }
        }
        $this->stream = null;
        $this->opened = false;
        $this->currentMsgIndex = null;
        $this->currentMsgData  = '';
        $this->currentMsgPtr   = 0;
        $this->hasDeletes = false;
        return '';
    }
}
