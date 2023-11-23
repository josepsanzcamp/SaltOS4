<?php

$lines = file("apps/tester/pepe.txt");
foreach ($lines as $index => $line) {
    $line = trim(substr($line,25));
    $data = json_decode($line, true);
    $temp = [
        "#attr" => [],
        "value" => [],
    ];
    foreach($data as $key => $val) {
        if (is_array($val)) {
            if (!count($val)) {
                unset($data[$key]);
                continue;
            }
            $temp["value"][$key] = $val;
        } else {
            if ($val == "") {
                unset($data[$key]);
                continue;
            }
            $temp["#attr"][$key] = $val;
        }
    }
    if (!count($temp["value"])) {
        $temp["value"] = "";
    }
    foreach (["datalist", "rows", "data", "header", "footer", "images"] as $x) {
        if (isset($temp["value"][$x])) {
            $temp["value"][$x] = [
                "#attr" => ["eval" => "true"],
                "value" => json_encode($temp["value"][$x]),
            ];
        }
    }
    if (isset($temp["value"]["value_old"])) {
        $temp["value"]["value_old"] = implode(", ", $temp["value"]["value_old"]);
    }
    //~ $temp["value"] = "";
    $type = $temp["#attr"]["type"];
    unset($temp["#attr"]["type"]);
    $temp = [$type => $temp];
    if ($index < 36) {
        $lines[$index] = __array2xml_write_nodes($temp, 0);
    } else {
        print_r($temp);
        file_put_contents("data/logs/pepe.txt", implode($lines));
        die();
    }
}
file_put_contents("data/logs/pepe.txt", implode($lines));
die();
