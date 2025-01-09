<?php

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects

function grep($data, $pattern)
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

function head($data, $lines)
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

function curl($url, $timeout)
{
    $firefox = '-H "User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:133.0) Gecko/20100101 Firefox/133.0"';
    ob_start();
    passthru("timeout $timeout curl $firefox -s $url");
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
$reset = "\e[0m";
$red = "\e[31m";
$green = "\e[32m";
foreach ($libs as $key => $lib) {
    $lib = explode('|', $lib);
    if (count($lib) == 4 && $lib[0][0] != '#' && (count($argv) == 0 || in_array($lib[0], $argv))) {
        //~ $temp=@file_get_contents($lib[1]);
        $start = microtime(true);
        $temp = curl($lib[1], 5);
        $end = microtime(true);
        $istimeout = (($end - $start) >= 5);
        $temp = trim($temp);
        $iserror = ($temp == '');
        $temp = str_replace('<TD><span ', "<TD>\n<span ", $temp); // FIX FOR WWW.PHPCLASSES.ORG
        $temp = str_replace('">', "\">\n", $temp); // FIX FOR WWW.PHPCLASSES.ORG
        $temp = str_replace('><svg', ">\n<svg", $temp); // FIX FOR SOURCEFORGE.NET
        $temp = str_replace('<title>Tags from', '', $temp); // FIX FOR GITHUB.COM
        $temp = grep($temp, $lib[2]);
        $temp = head($temp, 1);
        $temp = trim($temp);
        $isvoid = ($temp == '');
        $temp2 = grep($temp, base64_decode($lib[3]));
        $isko = ($temp2 == '');
        if ($istimeout) {
            echo "{$lib[0]}: {$red}timeout curl({$lib[1]}){$reset}\n";
        } elseif ($iserror) {
            echo "{$lib[0]}: {$red}error curl({$lib[1]}){$reset}\n";
        } elseif ($isvoid) {
            echo "{$lib[0]}: {$red}void curl({$lib[1]}){$reset}\n";
        } elseif ($isko) {
            echo "{$lib[0]}: {$red}KO{$reset}($temp)\n";
            $lib[3] = base64_encode($temp);
        } else {
            echo "{$lib[0]}: {$green}OK{$reset}\n";
            $lib[3] = base64_encode($temp);
        }
    }
    $lib = implode('|', $lib);
    $libs[$key] = $lib;
}
$libs = implode("\n", $libs);
$hash2 = md5($libs);
if ($hash1 != $hash2) {
    file_put_contents($file, $libs);
}
