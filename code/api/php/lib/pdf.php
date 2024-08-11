<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2024 by Josep Sanz Campderrós
 * More information in https://www.saltos.org or info@saltos.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration
// phpcs:disable PSR1.Methods.CamelCapsMethodName

/**
 * PDF helper module
 *
 * This fie contains useful functions related to PDF using tcpdf
 */

require_once "lib/tcpdf/vendor/autoload.php";

/**
 * TODO
 *
 * TODO
 */
class PDF extends TCPDF
{
    private $arr_header;
    private $row_header;
    private $arr_footer;
    private $row_footer;
    private $check_y_enabled;

    /**
     * TODO
     *
     * TODO
     */
    public function Init()
    {
        $this->Set_Header([], []);
        $this->Set_Footer([], []);
        $this->check_y_enable(true);
        //~ if (getDefault("ini_set")) {
            //~ eval_iniset(getDefault("ini_set"));
        //~ }
    }

    /**
     * TODO
     *
     * TODO
     */
    public function Set_Header($arr, $row)
    {
        $this->arr_header = $arr;
        $this->row_header = $row;
    }

    /**
     * TODO
     *
     * TODO
     */
    public function Set_Footer($arr, $row)
    {
        $this->arr_footer = $arr;
        $this->row_footer = $row;
    }

    /**
     * TODO
     *
     * TODO
     */
    public function Header()
    {
        $oldenable = $this->check_y_enable(false);
        __pdf_eval_pdftag($this->arr_header, $this->row_header);
        $this->check_y_enable($oldenable);
    }

    /**
     * TODO
     *
     * TODO
     */
    public function Footer()
    {
        $oldenable = $this->check_y_enable(false);
        __pdf_eval_pdftag($this->arr_footer, $this->row_footer);
        $this->check_y_enable($oldenable);
    }

    /**
     * TODO
     *
     * TODO
     */
    public function check_y($offset = 0)
    {
        if ($this->check_y_enabled) {
            if ($this->y + $offset > ($this->hPt / $this->k) - $this->bMargin) {
                $oldx = $this->GetX();
                $this->AddPage();
                $this->SetY($this->tMargin);
                $this->SetX($oldx);
            }
        }
    }

    /**
     * TODO
     *
     * TODO
     */
    public function check_y_enable($enable)
    {
        $retval = $this->check_y_enabled;
        $this->check_y_enabled = $enable;
        return $retval;
    }
}

/**
 * TODO
 *
 * TODO
 */
function __pdf_eval_value($input, $row, $pdf)
{
    return eval("return $input;");
}

/**
 * TODO
 *
 * TODO
 */
function __pdf_eval_array($array, $row, $pdf)
{
    foreach ($array as $key => $val) {
        $array[$key] = __pdf_eval_value($val, $row, $pdf);
    }
    return $array;
}

/**
 * TODO
 *
 * TODO
 */
function __pdf_eval_explode($separator, $str, $limit = 0)
{
    $result = [];
    $len = strlen($str);
    $ini = 0;
    $count = 0;
    $open = ["'" => 0, '"' => 0];
    $pars = 0;
    for ($i = 0; $i < $len; $i++) {
        $letter = $str[$i];
        if (array_key_exists($letter, $open)) {
            $open[$letter] = ($open[$letter] == 1) ? 0 : 1;
        } elseif ($letter == "(") {
            $pars++;
        } elseif ($letter == ")") {
            $pars--;
        }
        if ($letter == $separator && array_sum($open) + $pars == 0) {
            if ($limit > 0 && $count == $limit - 1) {
                $result[] = substr($str, $ini);
                $ini = $i;
                break;
            } else {
                $result[] = substr($str, $ini, $i - $ini);
                $ini = $i + 1;
                $count++;
            }
        }
    }
    if ($i != $ini) {
        $result[] = substr($str, $ini, $i - $ini);
    }
    return $result;
}

/**
 * TODO
 *
 * TODO
 */
function __pdf_eval_pdftag($array, $row = [])
{
    require_once "php/lib/color.php";
    static $pdf = null;
    // Support for ltr and rtl langs
    $dir = "ltr";
    if (isset($row["dir"])) {
        $dir = $row["dir"];
    }
    $rtl = ["ltr" => ["L" => "L", "C" => "C", "R" => "R"], "rtl" => ["L" => "R", "C" => "C", "R" => "L"]];
    $fonts = ["normal" => "atkinsonhyperlegible", "mono" => "dejavusansmono"];
    if (!is_array($array)) {
        show_php_error(["phperror" => "Array not found"]);
    }
    foreach ($array as $key => $val) {
        $key = strtok($key, "#");
        static $booleval = 1;
        switch ($key) {
            case "eval":
                $booleval = __pdf_eval_value($val, $row, $pdf);
                break;
            case "constructor":
                if (!$booleval) {
                    break;
                }
                $temp = __pdf_eval_array(__pdf_eval_explode(",", $val), $row, $pdf);
                $pdf = new PDF($temp[0], $temp[1], $temp[2]);
                $pdf->SetCreator(get_name_version_revision());
                $pdf->SetDisplayMode("fullwidth", "continuous");
                $pdf->setRTL($dir == "rtl");
                $pdf->Init();
                break;
            case "margins":
                if (!$booleval) {
                    break;
                }
                $temp = __pdf_eval_array(__pdf_eval_explode(",", $val), $row, $pdf);
                $pdf->SetMargins($temp[3], $temp[0], $temp[1]);
                $pdf->SetAutoPageBreak(true, $temp[2]);
                break;
            case "foreach":
                if (!$booleval) {
                    break;
                }
                if (!isset($val["query"])) {
                    show_php_error(["phperror" => "Foreach without query!!!"]);
                }
                $query = __pdf_eval_value($val["query"], $row, $pdf);
                unset($val["query"]);
                $result = db_query($query);
                while ($row2 = db_fetch_row($result)) {
                    __pdf_eval_pdftag($val, $row2);
                }
                db_free($result);
                break;
            case "output":
                if (!$booleval) {
                    break;
                }
                $name = __pdf_eval_value($val, $row, $pdf);
                $buffer = $pdf->Output($name, "S");
                return [
                    "name" => $name,
                    "data" => $buffer,
                ];
            case "header":
                if (!$booleval) {
                    break;
                }
                $pdf->Set_Header($val, $row);
                break;
            case "footer":
                if (!$booleval) {
                    break;
                }
                $pdf->Set_Footer($val, $row);
                break;
            case "newpage":
                if (!$booleval) {
                    break;
                }
                if ($val) {
                    $pdf->AddPage(__pdf_eval_value($val, $row, $pdf));
                } else {
                    $pdf->AddPage();
                }
                break;
            case "font":
                if (!$booleval) {
                    break;
                }
                $temp2 = __pdf_eval_array(__pdf_eval_explode(",", $val, 4), $row, $pdf);
                $temp = [$temp2[0], $temp2[1], $temp2[2],
                    color2dec($temp2[3], "R"), color2dec($temp2[3], "G"), color2dec($temp2[3], "B"),
                ];
                if (isset($fonts[$temp[0]])) {
                    $temp[0] = $fonts[$temp[0]];
                }
                $pdf->SetFont($temp[0], $temp[1], $temp[2]);
                $pdf->SetTextColor($temp[3], $temp[4], $temp[5]);
                break;
            case "image":
                if (!$booleval) {
                    break;
                }
                $temp = __pdf_eval_array(__pdf_eval_explode(",", $val, 6), $row, $pdf);
                if (isset($temp[5])) {
                    $pdf->StartTransform();
                }
                if (isset($temp[5])) {
                    $pdf->Rotate(floatval($temp[5]), $temp[0], $temp[1]);
                }
                if (!file_exists($temp[4])) {
                    show_php_error(["phperror" => "File {$temp[4]} not found"]);
                }
                $pdf->Image($temp[4], $temp[0], $temp[1], $temp[2], $temp[3]);
                if (isset($temp[5])) {
                    $pdf->StopTransform();
                }
                break;
            case "text":
                if (!$booleval) {
                    break;
                }
                $temp = __pdf_eval_array(__pdf_eval_explode(",", $val, 4), $row, $pdf);
                if (isset($temp[3])) {
                    $pdf->StartTransform();
                }
                if (isset($temp[3])) {
                    $pdf->Rotate(floatval($temp[3]), $temp[0], $temp[1]);
                }
                $pdf->SetXY($temp[0], $temp[1]);
                $pdf->Cell(0, 0, strval($temp[2]));
                if (isset($temp[3])) {
                    $pdf->StopTransform();
                }
                break;
            case "textarea":
                if (!$booleval) {
                    break;
                }
                $temp = __pdf_eval_array(__pdf_eval_explode(",", $val, 7), $row, $pdf);
                if (isset($temp[6])) {
                    $pdf->StartTransform();
                }
                if (isset($temp[6])) {
                    $pdf->Rotate(floatval($temp[6]), $temp[0], $temp[1]);
                }
                $pdf->SetXY($temp[0], $temp[1]);
                if (!isset($temp[6])) {
                    $pdf->check_y($temp[3]);
                }
                $pdf->MultiCell($temp[2], $temp[3], strval($temp[5]), 0, $rtl[$dir][$temp[4]]);
                if (isset($temp[6])) {
                    $pdf->StopTransform();
                }
                break;
            case "color":
                if (!$booleval) {
                    break;
                }
                $temp2 = __pdf_eval_array(__pdf_eval_explode(",", $val, 2), $row, $pdf);
                $temp = [color2dec($temp2[0], "R"), color2dec($temp2[0], "G"), color2dec($temp2[0], "B"),
                    color2dec($temp2[1], "R"), color2dec($temp2[1], "G"), color2dec($temp2[1], "B"),
                ];
                $pdf->SetDrawColor($temp[0], $temp[1], $temp[2]);
                $pdf->SetFillColor($temp[3], $temp[4], $temp[5]);
                break;
            case "rect":
                if (!$booleval) {
                    break;
                }
                $temp = __pdf_eval_array(__pdf_eval_explode(",", $val, 7), $row, $pdf);
                if (isset($temp[5])) {
                    $pdf->SetLineWidth($temp[5]);
                }
                if (isset($temp[6])) {
                    $pdf->RoundedRect($temp[0], $temp[1], $temp[2], $temp[3], $temp[6], "1111", $temp[4]);
                } else {
                    $pdf->Rect($temp[0], $temp[1], $temp[2], $temp[3], $temp[4]);
                }
                break;
            case "line":
                if (!$booleval) {
                    break;
                }
                $temp = __pdf_eval_array(__pdf_eval_explode(",", $val, 5), $row, $pdf);
                if (isset($temp[4])) {
                    $pdf->SetLineWidth($temp[4]);
                }
                $pdf->Line($temp[0], $temp[1], $temp[2], $temp[3]);
                break;
            case "setxy":
                if (!$booleval) {
                    break;
                }
                $temp = __pdf_eval_array(__pdf_eval_explode(",", $val, 2), $row, $pdf);
                $pdf->SetXY($temp[0], $temp[1]);
                $pdf->check_y();
                break;
            case "getxy":
                if (!$booleval) {
                    break;
                }
                $temp = __pdf_eval_array(__pdf_eval_explode(",", $val, 2), $row, $pdf);
                $row[$temp[0]] = $pdf->GetX();
                $row[$temp[1]] = $pdf->GetY();
                break;
            case "pageno":
                if (!$booleval) {
                    break;
                }
                $temp = __pdf_eval_array(__pdf_eval_explode(",", $val, 6), $row, $pdf);
                if (!isset($temp[4])) {
                    $pdf->SetXY($temp[0], $temp[1]);
                    if (!isset($temp[2])) {
                        $temp[2] = "%s/%s";
                    }
                    $pdf->Cell(0, 0, sprintf($temp[2], $pdf->getAliasNumPage(), $pdf->getAliasNbPages()));
                } else {
                     // TO FIX AN ALIGN BUG
                    if ($temp[4] == "C") {
                        $temp[0] += 7.5;
                    }
                    if ($temp[4] == "R") {
                        $temp[0] += 15;
                    }
                    // CONTINUE
                    $pdf->SetXY($temp[0], $temp[1]);
                    if (!isset($temp[5])) {
                        $temp[5] = "%s/%s";
                    }
                    $pdf->check_y($temp[3]);
                    $pdf->MultiCell(
                        $temp[2],
                        $temp[3],
                        sprintf($temp[5], $pdf->getAliasNumPage(), $pdf->getAliasNbPages()),
                        0,
                        $temp[4]
                    );
                }
                break;
            case "checky":
                if (!$booleval) {
                    break;
                }
                $temp = __pdf_eval_array(__pdf_eval_explode(",", $val, 1), $row, $pdf);
                $pdf->check_y($temp[0]);
                break;
            case "link":
                if (!$booleval) {
                    break;
                }
                $temp = __pdf_eval_array(__pdf_eval_explode(",", $val, 4), $row, $pdf);
                $pdf->SetXY($temp[0], $temp[1]);
                $pdf->Cell(0, 0, $temp[2], 0, 0, "", false, $temp[3]);
                break;
            default:
                show_php_error(["phperror" => "Eval PDF Tag error: $key"]);
        }
    }
    return $pdf;
}

/**
 * TODO
 *
 * TODO
 */
function pdf($file, $row)
{
    $xml = xmlfile2array($file);
    require_once "php/lib/pdf.php";
    $pdf = __pdf_eval_pdftag($xml, $row);
    return [
        "name" => $pdf["name"],
        "type" => "application/pdf",
        "data" => base64_encode($pdf["data"]),
    ];
}
