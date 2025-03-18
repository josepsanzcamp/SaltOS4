<?php

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects

$files = array_merge(glob('/tmp/nyc_output/*/coverage-final.json'), glob('/tmp/nyc_output/*/out.json'));
foreach ($files as $file) {
    $json = json_decode(file_get_contents($file), true);
    foreach ($json as $key => $val) {
        if ($key != $val['path']) {
            echo "Internal error 1!!!\n";
            die();
        }
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
        $json[$key]['path'] = $temp[0];
    }
    $file2 = str_replace('.json', '.new.json', $file);
    file_put_contents($file2, json_encode($json));
}

function terminal($cmd)
{
    passthru('script -q -c "COLUMNS=$(tput cols); FORCE_COLOR=1 ' . $cmd . '" /dev/null');
}

terminal('istanbul-merge --out /tmp/jest.report/coverage-final.json /tmp/nyc_output/*/*.new.json');
terminal('nyc report --temp-dir=/tmp/jest.report --reporter=html --report-dir=/tmp/jest.report/html');
terminal('nyc report --temp-dir=/tmp/jest.report --reporter=text');
