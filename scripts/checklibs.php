<?php

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects

function grep($pattern, $data)
{
    $data = explode("\n", $data);
    $result = [];
    foreach ($data as $d) {
        if (stripos($d, $pattern) !== false) {
            $result[] = $d;
        }
    }
    $result = implode("\n", $result);
    return $result;
}

function head($lines, $data)
{
    $data = explode("\n", $data);
    $result = [];
    foreach ($data as $line => $d) {
        if ($line < $lines) {
            $result[] = $d;
        }
    }
    $result = implode("\n", $result);
    return $result;
}

function wget($url)
{
    ob_start();
    passthru("wget -O - -q $url");
    $buffer = ob_get_clean();
    return $buffer;
}

if (!isset($argv[1])) {
    die("Unknown filename\n");
}
$file = $argv[1];
if (!file_exists($file)) {
    die("{$file} not found\n");
}
$libs = file_get_contents($file);
$hash1 = md5($libs);
$libs = explode("\n", $libs);
array_shift($argv);
array_shift($argv);
foreach ($libs as $key => $lib) {
    $lib = explode("|", $lib);
    if (count($lib) == 4 && $lib[0][0] != "#" && (count($argv) == 0 || in_array($lib[0], $argv))) {
        //~ $temp=@file_get_contents($lib[1]);
        $temp = wget($lib[1]);
        $iserror = ($temp == "") ? 1 : 0;
        $temp = str_replace("</TH>\n<TD>", "</TH><TD>", $temp); // FIX FOR WWW.PHPCLASSES.ORG
        $temp = str_replace("</th>\n<td>", "</th><td>", $temp); // FIX FOR WWW.PHPCLASSES.ORG
        $temp = str_replace("><svg", ">\n<svg", $temp); // FIX FOR SOURCEFORGE.NET
        $temp = grep($lib[2], $temp);
        $temp = head(1, $temp);
        if (substr($lib[3], 0, 7) != "base64:") {
            $temp2 = grep($lib[3], $temp);
        } else {
            $temp2 = grep(base64_decode(substr($lib[3], 7)), $temp);
        }
        $isko = ($temp2 == "") ? 1 : 0;
        if ($iserror) {
            echo $lib[0] . ": " . "\033[31m!file_get_contents(" . $lib[1] . ")\033[0m" . "\n";
        } elseif ($isko) {
            echo $lib[0] . ": " . "\033[31mKO\033[0m" . " (" . trim($temp) . ")" . "\n";
            $lib[3] = "base64:" . base64_encode(trim($temp));
        } else {
            echo $lib[0] . ": " . "\033[32mOK\033[0m" . "\n";
        }
    }
    $lib = implode("|", $lib);
    $libs[$key] = $lib;
}
$libs = implode("\n", $libs);
$hash2 = md5($libs);
if ($hash1 != $hash2) {
    file_put_contents($file, $libs);
}
