<?php

if ($argc != 2) {
    echo "Error en los argumentos, falta el fichero js\n";
    die();
}

$file = $argv[1];

// First part => prepare the file to phpcs and execute it
$buffer = file_get_contents($file);
$buffer = explode("`", $buffer);

$pos = 1;
$count = count($buffer);
if ($count % 2 == 0) {
    $pos++;
}

for ($i = $pos; $i < $count; $i += 2) {
    $hash = md5($buffer[$i]);
    $key = "__HASH_${hash}__";
    $buffer[$i] = $key;
}

$buffer = implode("`", $buffer);
file_put_contents("$file.php.js", $buffer);
passthru("phpcs $file.php.js");
unlink("$file.php.js");

// Second part => check the space after comma
$buffer = file_get_contents($file);
$buffer = explode("\n", $buffer);

foreach ($buffer as $key => $val) {
    $val = trim($val);
    // Exceptions
    if ($val == "") {
        continue;
    }
    if (strpos($val, '__,_|') !== false) {
        continue;
    }
    if (strpos($val, 'join(",")') !== false) {
        continue;
    }
    if (strpos($val, 'split(",")') !== false) {
        continue;
    }
    if (strpos($val, '[","]') !== false) {
        continue;
    }
    // Normal operation
    $val2 = explode(",", $val);
    unset($val2[0]);
    $error = 0;
    foreach ($val2 as $val3) {
        if (!strlen($val3)) {
            continue;
        }
        if (substr($val3, 0, 1) != " ") {
            $error = 1;
            break;
        }
    }
    if ($error) {
        $key++;
        echo "$file:$key:$val\n";
    }
}
