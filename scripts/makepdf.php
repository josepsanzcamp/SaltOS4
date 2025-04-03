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

// Prepare the directory to work
if (!file_exists($outdir)) {
    mkdir($outdir);
}
chdir($outdir);

// PDF Section
$file = str_replace('.t2t', '', $outfile);
ob_passthru("txt2tags --toc -t tex -i ${file}.t2t -o ${file}.tex");
$buffer = file_get_contents("${file}.tex");
$buffer = explode("\n", $buffer);
$buffer0 = array_slice($buffer, 0, 5);
$buffer0 = str_replace('\\documentclass{article}', '\\documentclass[a4paper]{article}', $buffer0);
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
    '\\allsectionsfont{\\color{myblue}}',
    '\\definecolor{myblue}{RGB}{39,128,227}',
    '\\setlength{\\parindent}{0mm}',
    '\\setlength{\\parskip}{3mm}',
    '\\setlength{\\plparsep}{2.5mm}',
    '\\def\\htmladdnormallink#1#2{\\href{#2}{#1}}',
    '\\definecolor{mygrey}{rgb}{0.9,0.9,0.9}',
    "\\usepackage{courier}",
    "\\lstset{basicstyle=\\ttfamily,backgroundcolor=\\color{mygrey},breaklines=true}",
    "\\usepackage{tocloft}",
    '\\setlength{\\cftsubsubsecnumwidth}{13mm}',
    '\\setlength\\cftparskip{3mm}',
    '',
];
$buffer2 = array_slice($buffer, 5);
$buffer2 = str_replace("\\begin{verbatim}", "\\begin{lstlisting}", $buffer2);
$buffer2 = str_replace("\\end{verbatim}", "\\end{lstlisting}", $buffer2);
$buffer2 = str_replace("\t", str_repeat(' ', 4), $buffer2);
$buffer2 = str_replace('\\item', "\\item[\\color{myblue}\$\\bullet\$]", $buffer2);
$buffer = array_merge($buffer0, $buffer1, $buffer2);
$buffer = implode("\n", $buffer);
// FIX FOR THE IMAGE POSITION
$pos = strpos($buffer, '\\includegraphics{');
while ($pos !== false) {
    $pos2 = strpos($buffer, '}', $pos);
    $data = substr($buffer, $pos + 17, $pos2 - $pos - 17);
    $latex = "\\begin{center}\\includegraphics[width=0.5\\textwidth]{" . $data . "}\\end{center}";
    if (isset($removeimages) && $removeimages) {
        $latex = '';
    }
    $buffer = substr_replace($buffer, $latex, $pos, $pos2 - $pos + 1);
    $pos = strpos($buffer, '\\includegraphics{', $pos);
}
// CONTINUE
file_put_contents("${file}.tex", $buffer);
for ($i = 0; $i < 3; $i++) {
    ob_passthru("pdflatex ${file}.tex");
}
$exts = ['aux', 'log', 'out', 'toc'];
foreach ($exts as $ext) {
    unlink("${file}.${ext}");
}
