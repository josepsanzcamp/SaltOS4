<?php

declare(strict_types=1);

$file = 'vendor/tecnickcom/tcpdf/config/tcpdf_config.php';
require_once $file;
if (K_TCPDF_THROW_EXCEPTION_ERROR) {
    echo "tcpdf_config.php is ok, nothing to do!!!\n";
    die();
}

echo "tcpdf_config.php contains K_TCPDF_THROW_EXCEPTION_ERROR as false instead of true\n";
$buffer = file_get_contents($file);
$buffer = str_replace(
    "define('K_TCPDF_THROW_EXCEPTION_ERROR', false);",
    "define('K_TCPDF_THROW_EXCEPTION_ERROR', true);",
    $buffer
);
file_put_contents($file, $buffer);
echo "Applied fix to $file\n";
