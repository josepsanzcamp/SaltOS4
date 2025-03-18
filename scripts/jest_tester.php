<?php

declare(strict_types=1);

chdir('code/api');
foreach (glob('php/autoload/*.php') as $file) {
    if (basename($file) == 'zindex.php') {
        continue;
    }
    require $file;
}

init_timer();
init_random();

global $_CONFIG;
$_CONFIG = eval_attr(xmlfiles2array(detect_config_files('xml/config.xml')));
db_connect();

$orig = 'apps/tester/xml/tester.xml';
$path = 'main/layout/container';
$dest = '/tmp/tester.json';

$array = xmlfile2array($orig);
$array = xpath_search_first_value($path, $array);
$array = eval_attr($array);
$label = [];
foreach ($array as $key => $val) {
    $val = join_attr_value($val);
    $val['type'] = fix_key($key);
    if ($val['type'] == 'hidden') {
        unset($array[$key]);
        continue;
    }
    if (!isset($val['id'])) {
        unset($array[$key]);
        continue;
    }
    $array[$key] = $val;
    if (isset($label[$val['label']])) {
        echo "WARNING: {$val['label']} FOUND!!!\n";
    }
    $label[$val['label']] = $val['label'];
}
$array = array_values($array);
$json = json_encode($array);
file_put_contents($dest, $json);
