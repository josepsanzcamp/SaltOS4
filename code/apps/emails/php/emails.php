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

/**
 * TODO
 *
 * TODO
 */

/**
 * TODO
 *
 * TODO
 */
function make_where_query_emails($json)
{
    // Prepare fields
    $account_id = $json["account_id"] ?? "";
    $fields = $json["fields"] ?? "";
    $search = $json["search"] ?? "";
    $date1 = $json["date1"] ?? "";
    $date2 = $json["date2"] ?? "";
    $date3 = $json["date3"] ?? "";
    $onlynew = $json["onlynew"] ?? "";
    $onlywait = $json["onlywait"] ?? "";
    $onlyspam = $json["onlyspam"] ?? "";
    $hidespam = $json["hidespam"] ?? "";
    $withfiles = $json["withfiles"] ?? "";
    $withoutfiles = $json["withoutfiles"] ?? "";
    $onlyinbox = $json["onlyinbox"] ?? "";
    $onlyoutbox = $json["onlyoutbox"] ?? "";
    // Some helper fields
    $fields_array = [
        "" => "",
        "email" => "`from`,`to`,cc,bcc",
        "subject" => "subject",
        "body" => "body",
    ];
    $fields = isset($fields_array[$fields]) ? $fields : "";
    $date3_array = [
        "today" => "DATE(datetime)=DATE('" . current_date() . "')",
        "yesterday" => "DATE(datetime)=DATE('" . current_date(-86400) . "')",
        "week" => "DATE(datetime)>=DATE('" . current_date(-86400 * 7) . "')",
        "month" => "DATE(datetime)>=DATE('" . current_date(-86400 * 30) . "')",
    ];
    $date3 = isset($date3_array[$date3]) ? $date3 : "";
    $only = intval($onlynew) . intval($onlywait) . intval($onlyspam);
    $only_array = [
        "100" => "state_new='1'",
        "010" => "state_wait='1'",
        "110" => "(state_new='1' OR state_wait='1')",
        "001" => "state_spam='1'",
        "101" => "(state_new='1' OR state_spam='1')",
        "011" => "(state_wait='1' OR state_spam='1')",
        "111" => "(state_new='1' OR state_wait='1' OR state_spam='1')",
    ];
    $only = isset($only_array[$only]) ? $only : "";
    // Make the query part
    $query = ["1=1"];
    if ($account_id != "") {
        $query[] = "account_id='$account_id'";
    }
    if ($fields != "") {
        $query[] = make_like_query($fields_array[$fields], $search);
    }
    if ($only != "") {
        $query[] = $only_array[$only];
    }
    if ($hidespam) {
        $query[] = "state_spam='0'";
    }
    if ($withfiles) {
        $query[] = "files>0";
    }
    if ($withoutfiles) {
        $query[] = "files=0";
    }
    if ($onlyinbox) {
        $query[] = "is_outbox=0";
    }
    if ($onlyoutbox) {
        $query[] = "is_outbox=1";
    }
    if ($date1 != "") {
        $query[] = "(DATE(datetime)>=DATE('$date1'))";
    }
    if ($date2 != "") {
        $query[] = "(DATE('$date2')>=DATE(datetime))";
    }
    if ($date3 != "") {
        $query[] = $date3_array[$date3];
    }
    $query = implode(" AND ", $query);
    return $query;
}
