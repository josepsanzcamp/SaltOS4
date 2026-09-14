<?php

declare(strict_types=1);

/**
 * PHPUnit bootstrap for the TCPDF compatibility facade test suite.
 *
 * Loads the facade (which loads tcpdf_autoconfig.php and the Composer
 * autoloader for the tc-lib-* dependencies).
 *
 * @package com.tecnick.tcpdf
 */

// Error() terminates the process by default; tests need it to throw instead.
if (!defined('K_TCPDF_THROW_EXCEPTION_ERROR')) {
    define('K_TCPDF_THROW_EXCEPTION_ERROR', true);
}

require_once dirname(__DIR__) . '/tcpdf.php';
