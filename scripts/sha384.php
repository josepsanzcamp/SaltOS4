<?php

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects

function ob_passthru($cmd)
{
    ob_start();
    passthru($cmd);
    return trim(ob_get_clean());
}

$buffer = file_get_contents('php://stdin');
//$command = "cat __FILE__ | openssl dgst -sha384 -binary | openssl base64 -A";
$command = 'sha384sum __FILE__ | head -c 96 | xxd -r -p | base64';
$buffer = explode("\n", $buffer);
foreach ($buffer as $key => $val) {
    if (strpos($val, 'integrity=""') === false) {
        continue;
    }
    $temp = explode(' ', str_replace('"', ' ', $val));
    for ($i = 0; $i < count($temp); $i++) {
        if (in_array($temp[$i], ['src=', 'href='])) {
            $sha384 = ob_passthru(str_replace('__FILE__', 'code/web/' . $temp[$i + 1], $command));
            $buffer[$key] = str_replace('integrity=""', "integrity=\"sha384-$sha384\"", $buffer[$key]);
            $hash = md5_file('code/web/' . $temp[$i + 1]);
            $buffer[$key] = str_replace($temp[$i + 1], $temp[$i + 1] . '?' . $hash, $buffer[$key]);
        }
    }
}
$buffer = implode("\n", $buffer);
echo $buffer;
