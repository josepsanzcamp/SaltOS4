<?php

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects

function encode_bad_chars($cad, $pad = '_', $extra = '')
{
    $orig = [
        'á', 'à', 'ä', 'â', 'é', 'è', 'ë', 'ê', 'í', 'ì', 'ï', 'î',
        'ó', 'ò', 'ö', 'ô', 'ú', 'ù', 'ü', 'û', 'ñ', 'ç',
        'Á', 'À', 'Ä', 'Â', 'É', 'È', 'Ë', 'Ê', 'Í', 'Ì', 'Ï', 'Î',
        'Ó', 'Ò', 'Ö', 'Ô', 'Ú', 'Ù', 'Ü', 'Û', 'Ñ', 'Ç',
    ];
    $dest = [
        'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i',
        'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'n', 'c',
        'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i',
        'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'n', 'c',
    ];
    $cad = str_replace($orig, $dest, $cad);
    $cad = strtolower($cad);
    $len = strlen($cad);
    for ($i = 0; $i < $len; $i++) {
        $letter = $cad[$i];
        $replace = true;
        if ($letter >= 'a' && $letter <= 'z') {
            $replace = false;
        }
        if ($letter >= '0' && $letter <= '9') {
            $replace = false;
        }
        if (strpos($extra, $letter) !== false) {
            $replace = false;
        }
        if ($replace) {
            $cad[$i] = $pad;
        }
    }
    $cad = trim_words($cad, $pad);
    return $cad;
}

function trim_words($cad, $pad = ' ')
{
    do {
        $len1 = strlen($cad);
        $cad = str_replace($pad . $pad, $pad, $cad);
        $len2 = strlen($cad);
    } while ($len1 - $len2 > 0);
    $len = strlen($pad);
    if (substr($cad, 0, $len) == $pad) {
        $cad = substr($cad, $len);
    }
    if (substr($cad, -$len, $len) == $pad) {
        $cad = substr($cad, 0, -$len);
    }
    return $cad;
}

function ob_passthru($cmd)
{
    ob_start();
    passthru("$cmd 2>&1");
    return ob_get_clean();
}

// Prepare the files to use and output variables
array_shift($argv);
$outfile = array_shift($argv);
$jsonfile = array_shift($argv);

// Prepare the json
$json = json_decode(file_get_contents($jsonfile), true);
$dict = [];
foreach($json as $item) {
    $type = $item['type'];
    if (isset($dict[$type])) {
        continue;
    }
    $label = encode_bad_chars($item['label'], '-');
    $file = "ujest/snaps/test-bootstrap-js-bootstrap-$label-1-snap.png";
    if (file_exists($file)) {
        $dict[$type] = $file;
    }
}
//~ print_r($dict);
//~ die();

// Get the source and process it
$buffer = file_get_contents($outfile);
$hash1 = md5($buffer);
$buffer = explode("\n", $buffer);
foreach ($buffer as $key => $val) {
    if (strncmp('saltos.bootstrap.__field.', $val, 25) === 0) {
        if ($buffer[$key - 1] == '```' && $buffer[$key + 1] == '```') {
            $type = strtok(substr($val, 25), ' ');
            if (isset($dict[$type])) {
                $buffer[$key + 2] = "\n[../{$dict[$type]}]\n";
            }
        }
    }
}
//~ print_r($buffer);
//~ die();
$buffer = implode("\n", $buffer);
$hash2 = md5($buffer);
if ($hash1 != $hash2) {
    file_put_contents($outfile, $buffer);
}
