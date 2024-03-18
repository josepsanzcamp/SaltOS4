<?php

declare(strict_types=1);

$buffer = file_get_contents("php://stdin");
$buffer = explode("\n", $buffer);
foreach ($buffer as $key => $val) {
    if (strpos($val, "'lib/") === false) {
        continue;
    }
    $temp = explode(" ", str_replace("'", " ", $val));
    for ($i = 0; $i < count($temp); $i++) {
        if (substr($temp[$i], 0, 4) == "lib/") {
            $hash = md5_file("code/web/" . $temp[$i]);
            $buffer[$key] = str_replace($temp[$i], $temp[$i] . "?" . $hash, $buffer[$key]);
        }
    }
}
$buffer = implode("\n", $buffer);
echo $buffer;
