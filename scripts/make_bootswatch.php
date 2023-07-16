<?php

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects

function encode_bad_chars($cad, $pad = "_", $extra = "")
{
    static $orig = [
        "á","à","ä","â","é","è","ë","ê","í","ì","ï","î","ó","ò","ö","ô","ú","ù","ü","û","ñ","ç",
        "Á","À","Ä","Â","É","È","Ë","Ê","Í","Ì","Ï","Î","Ó","Ò","Ö","Ô","Ú","Ù","Ü","Û","Ñ","Ç",
    ];
    static $dest = [
        "a","a","a","a","e","e","e","e","i","i","i","i","o","o","o","o","u","u","u","u","n","c",
        "a","a","a","a","e","e","e","e","i","i","i","i","o","o","o","o","u","u","u","u","n","c",
    ];
    $cad = str_replace($orig, $dest, $cad);
    $cad = strtolower($cad);
    $len = strlen($cad);
    for ($i = 0; $i < $len; $i++) {
        $letter = $cad[$i];
        $replace = 1;
        if ($letter >= "a" && $letter <= "z") {
            $replace = 0;
        }
        if ($letter >= "0" && $letter <= "9") {
            $replace = 0;
        }
        if (strpos($extra, $letter) !== false) {
            $replace = 0;
        }
        if ($replace) {
            $cad[$i] = $pad;
        }
    }
    $cad = prepare_words($cad, $pad);
    return $cad;
}

function prepare_words($cad, $pad = " ")
{
    $count = 1;
    while ($count) {
        $cad = str_replace($pad . $pad, $pad, $cad, $count);
    }
    $len = strlen($pad);
    if (substr($cad, 0, $len) == $pad) {
        $cad = substr($cad, $len);
    }
    if (substr($cad, -$len, $len) == $pad) {
        $cad = substr($cad, 0, -$len);
    }
    return $cad;
}

function wget($url1, $url2)
{
    ob_start();
    passthru("wget -O '$url2' -q '$url1'");
    $buffer = ob_get_clean();
    return $buffer;
}

$files = glob("*.css");
foreach ($files as $file) {
    $buffer = file_get_contents($file);
    $pos1 = strpos($buffer, "@import url(https://");
    $pos2 = strpos($buffer, ")", $pos1);
    if ($pos1 === false) {
        continue;
    }
    $pos1 += 12;
    $url1 = substr($buffer, $pos1, $pos2 - $pos1);
    echo $url1 . "\n";
    $url2 = "fonts/" . encode_bad_chars($url1);
    echo $url2 . "\n";
    if (!file_exists($url2)) {
        echo wget($url1, $url2);
    }
    $hash1 = md5($buffer);
    $buffer = str_replace($url1, $url2, $buffer);
    $hash2 = md5($buffer);
    if ($hash1 != $hash2) {
        file_put_contents($file, $buffer);
    }
}

$dir = getcwd();
chdir("fonts");
$files = glob("*css*");
foreach ($files as $file) {
    for (;;) {
        $buffer = file_get_contents($file);
        $pos1 = strpos($buffer, "src: url(https://");
        $pos2 = strpos($buffer, ")", $pos1);
        if ($pos1 === false) {
            break;
        }
        $pos1 += 9;
        $url1 = substr($buffer, $pos1, $pos2 - $pos1);
        $url1 = substr($buffer, $pos1, $pos2 - $pos1);
        echo $url1 . "\n";
        $url2 = encode_bad_chars($url1);
        echo $url2 . "\n";
        if (!file_exists($url2)) {
            echo wget($url1, $url2);
        }
        $hash1 = md5($buffer);
        $buffer = str_replace($url1, $url2, $buffer);
        $hash2 = md5($buffer);
        if ($hash1 != $hash2) {
            file_put_contents($file, $buffer);
        }
    }
}
chdir($dir);
