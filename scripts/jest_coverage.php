<?php

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects

// This part of code creates a lines dictionary, intended to filter the coverage provided
// by puppeteer that reports all lines instead of there are comments and void lines
$files = glob('code/web/js/*.js');
$lines = [];
foreach ($files as $file) {
    passthru("acorn --locations --compact --ecma2020 $file > /tmp/acorn.json");
    $base = basename($file);
    $lines[$base] = [];
    $json = json_decode(file_get_contents('/tmp/acorn.json'), true);
    foreach ($json['body'] as $body) {
        // This remove the 'use strict' line like does jest
        if (isset($body['expression']['raw']) && $body['expression']['raw'] == "'use strict'") {
            continue;
        }
        // This computes the range and defines the lines
        $start = $body['loc']['start']['line'];
        $end = $body['loc']['end']['line'];
        for ($i = $start; $i <= $end; $i++) {
            $lines[$base][$i] = $i;
        }
    }
}

// This part of code creates a new json file that only contains the files of the code/web/js/*.js
// directory, this is because we are mixing some files provided by differents coverage engines and
// we need to uniform the path item, too because from here we are filtering and fixing some spurious
// issues caused by the coverage provided by puppeteer that contains all commends and void lines too
$files = array_merge(glob('/tmp/nyc_output/*/coverage-final.json'), glob('/tmp/nyc_output/*/out.json'));
foreach ($files as $file) {
    $json = json_decode(file_get_contents($file), true);
    foreach ($json as $key => $val) {
        // This part is for detect internal errors, the key must to be the path
        if ($key != $val['path']) {
            echo "Internal error 1!!!\n";
            die();
        }
        // This part is for validate that we want this file in our report
        // and only one file can be found by glob
        $base = basename($key);
        $temp = glob("code/web/js/$base");
        if (count($temp) > 1) {
            echo "Internal error 2!!!\n";
            die();
        }
        if (!count($temp)) {
            unset($json[$key]);
            continue;
        }
        // This part apply the lines filter prepared before, this only checks that start line
        // is found in our dictionary, otherwise unset from statementMap and s arrays entries
        $json[$key]['path'] = $temp[0];
        foreach ($val['statementMap'] as $key2 => $val2) {
            $start = $val2['start']['line'];
            if (!isset($lines[$base][$start])) {
                unset($json[$key]['statementMap'][$key2]);
                unset($json[$key]['s'][$key2]);
            }
        }
    }
    $file2 = str_replace('.json', '.new.json', $file);
    file_put_contents($file2, json_encode($json));
}

function terminal($cmd)
{
    // This trick (script with columns and force_color) allow to see the output of the commands
    // like a real terminal.
    passthru('script -q -c "COLUMNS=$(tput cols); FORCE_COLOR=1 ' . $cmd . '" /dev/null');
}

// This part merge all files into only one and generages the html and text reports using istanbul
terminal('istanbul-merge --out /tmp/jest.report/coverage-final.json /tmp/nyc_output/*/*.new.json');
terminal('nyc report --temp-dir=/tmp/jest.report --reporter=html --report-dir=/tmp/jest.report/html');
terminal('nyc report --temp-dir=/tmp/jest.report --reporter=text');
