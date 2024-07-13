<?php

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects

$files = glob("*.css");
foreach ($files as $file) {
    $buffer = file_get_contents($file);
    $pos1 = strpos($buffer, "@import url(https://");
    if ($pos1 === false) {
        continue;
    }
    echo "Processing $file ...\n";
    $pos2 = strpos($buffer, ")", $pos1);
    $pos2++;
    if ($buffer[$pos2] == ";") {
        $pos2++;
    }
    $buffer = substr_replace($buffer, "", $pos1, $pos2 - $pos1);
    file_put_contents($file, $buffer);
}
