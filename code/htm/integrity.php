<?php

// phpcs:disable PSR1.Files.SideEffects

function ob_passthru($cmd)
{
    ob_start();
    passthru($cmd);
    return trim(ob_get_clean());
}

$buffer = file_get_contents('php://stdin');
$files = array(
    "lib/bootstrap/bootstrap.min.css",
    "lib/bootstrap/bootstrap.bundle.min.js",
    "js/bootstrap.min.js",
);
$command = "cat __FILE__ | openssl dgst -sha384 -binary | openssl base64 -A";
foreach ($files as $file) {
    $sha384 = ob_passthru("cd ..; " . str_replace("__FILE__", $file, $command));
    $buffer = str_replace("sha384-$file", "sha384-$sha384", $buffer);
}
echo $buffer;
