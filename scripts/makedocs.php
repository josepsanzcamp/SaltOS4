<?php

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects

function ob_passthru($cmd)
{
    ob_start();
    passthru("$cmd 2>&1");
    return ob_get_clean();
}

// Prepare the files to use and output variables
array_shift($argv);
$temp = array_shift($argv);
$outdir = dirname($temp);
$outfile = basename($temp);
$files = [];
foreach ($argv as $path) {
    $temp = explode("\n", trim(ob_passthru("find $path -type f")));
    sort($temp);
    $files = array_merge($files, $temp);
}
$files = array_flip($files);
//~ $files = ["core/php/autoload/import.php" => ""];
//~ print_r($files);
//~ die();

// Process all files to prepare the data in a memory structure
$open = "/**\n";
$close = " */\n";
foreach ($files as $file => $temp) {
    $buffer = file_get_contents($file);
    $matches = [];
    $pos = strpos($buffer, $open);
    while ($pos !== false) {
        $pos2 = strpos($buffer, $close, $pos + strlen($open));
        $pos3 = strpos($buffer, "\n", $pos2 + strlen($close));
        $a = trim(substr($buffer, $pos2 + strlen($close), $pos3 - $pos2 - strlen($close)));
        if (substr($a, -5, 5) == " => {") {
            $a = substr($a, 0, -5);
        }
        if ($a == "(()") {
            $a = "";
        }
        $b = trim(substr($buffer, $pos, $pos2 - $pos + strlen($close)));
        $b = explode("\n", $b);
        array_shift($b);
        array_pop($b);
        foreach ($b as $key => $val) {
            $val = trim($val);
            if ($val == "*") {
                $val = "";
            } elseif (substr($val, 0, 2) == "* ") {
                $val = substr($val, 2);
            }
            if (in_array(substr($val, 0, 1), ["@", "+", "-"]) && !in_array(substr($val, 1, 1), ["", " "])) {
                $val = "- $val";
            }
            $b[$key] = $val;
        }
        $c = array_shift($b);
        array_shift($b);
        // To add newlines to break the lists
        $is_inside_list = false;
        foreach ($b as $key => $val) {
            if (substr($val, 0, 1) == "-") {
                $is_inside_list = true;
            } elseif ($val == "" && $is_inside_list) {
                $b[$key] = "\n";
                $is_inside_list = false;
            }
        }
        // Continue
        $b = implode("\n", $b);
        $matches[] = [$c, $a, $b];
        $pos = strpos($buffer, $open, $pos2 + strlen($close));
    }
    if (!count($matches)) {
        unset($files[$file]);
        continue;
    }
    array_shift($matches);
    $files[$file] = $matches;
}
//~ print_r($files);
//~ die();

// T2T Section
ob_start();
echo "Code documentation\n";
$rev = intval(ob_passthru("svnversion"));
echo "SaltOS 4.0 r$rev\n";
$date = date("F Y");
echo "$date\n";
echo "\n";
echo "\n";
echo "\n";
foreach ($files as $file => $contents) {
    $first = true;
    foreach ($contents as $content) {
        if ($first) {
            echo "+{$content[0]}+\n";
            echo "\n";
            $content[1] = $file;
            $first = false;
        } else {
            echo "++{$content[0]}++\n";
            echo "\n";
        }
        if ($content[1] != "") {
            echo "```\n";
            echo "{$content[1]}\n";
            echo "```\n";
            echo "\n";
        }
        if ($content[2] != "") {
            echo "{$content[2]}\n";
            echo "\n";
        }
        echo "\n";
    }
}
$buffer = ob_get_clean();
//~ echo $buffer;
//~ die();
mkdir($outdir);
chdir($outdir);
file_put_contents($outfile, $buffer);
//~ die();

// HTML Section
$file = str_replace(".t2t", "", $outfile);
exec("txt2tags --toc -t html -i ${file}.t2t -o ${file}.html");
$buffer = file_get_contents("${file}.html");
$buffer = explode("\n", $buffer);
$buffer0 = array_slice($buffer, 0, 20);
$buffer1 = [
    "pre{background-color:#e6e6e6;padding:5px 3px}",
];
$buffer2 = array_slice($buffer, 20);
$buffer = array_merge($buffer0, $buffer1, $buffer2);
$buffer = implode("\n", $buffer);
file_put_contents("${file}.html", $buffer);
//~ die();

// PDF Section
$file = str_replace(".t2t", "", $outfile);
exec("txt2tags --toc -t tex -i ${file}.t2t -o ${file}.tex");
$buffer = file_get_contents("${file}.tex");
$buffer = explode("\n", $buffer);
$buffer0 = array_slice($buffer, 0, 5);
$buffer0 = str_replace("\\documentclass{article}", "\\documentclass[a4paper]{article}", $buffer0);
$buffer0 = str_replace(
    "\\usepackage[urlcolor=blue,colorlinks=true]{hyperref}",
    "\\usepackage[urlcolor=myblue,colorlinks=true,linkcolor=myblue]{hyperref}",
    $buffer0
);
$buffer1 = [
    "\\usepackage[english]{babel}",
    "\\usepackage{ucs}",
    "\\usepackage[utf8x]{inputenc}",
    "\\usepackage{eurosym}",
    "\\usepackage{sans}",
    "\\usepackage{fullpage}",
    "\\usepackage{listings}",
    "\\usepackage{xcolor}",
    "\\usepackage{sectsty}",
    "\\allsectionsfont{\\color{myblue}}",
    "\\definecolor{myblue}{RGB}{39,128,227}",
    "\\setlength{\\parindent}{0mm}",
    "\\setlength{\\parskip}{3mm}",
    "\\setlength{\\plparsep}{2.5mm}",
    "\\def\\htmladdnormallink#1#2{\\href{#2}{#1}}",
    "\\definecolor{mygrey}{rgb}{0.9,0.9,0.9}",
    "\\usepackage{courier}",
    "\\lstset{basicstyle=\\ttfamily,backgroundcolor=\\color{mygrey},breaklines=true}",
    "\\usepackage{tocloft}",
    "\\usepackage{calc}",
    "\\setlength{\\cftsubsecnumwidth}{\\widthof{\\large\\bfseries{}12.34}}",
    "\\setlength\\cftparskip{3mm}",
    "",
];
$buffer2 = array_slice($buffer, 5);
$buffer2 = str_replace("\\begin{verbatim}", "\\begin{lstlisting}", $buffer2);
$buffer2 = str_replace("\\end{verbatim}", "\\end{lstlisting}", $buffer2);
$buffer2 = str_replace("\t", str_repeat(" ", 4), $buffer2);
$buffer2 = str_replace("\\item", "\\item[\\color{myblue}\$\\bullet\$]", $buffer2);
// Fix for the propblem with spaces in the TOC between numbers and titles
//~ $buffer2 = str_replace(
    //~ "\\tableofcontents",
    //~ implode("\n", [
        //~ "\\begingroup",
        //~ "\\let\\orignumberline\\numberline",
        //~ "\\def\\numberline#1{\\orignumberline{#1}\\hspace{1ex}}",
        //~ "\\tableofcontents",
        //~ "\\endgroup",
    //~ ]),
    //~ $buffer2
//~ );
$buffer = array_merge($buffer0, $buffer1, $buffer2);
$buffer = implode("\n", $buffer);
file_put_contents("${file}.tex", $buffer);
for ($i = 0; $i < 3; $i++) {
    exec("pdflatex ${file}.tex");
}
$exts = ["aux", "log", "out", "toc"];
foreach ($exts as $ext) {
    unlink("${file}.${ext}");
}
