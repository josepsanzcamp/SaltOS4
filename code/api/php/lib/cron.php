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
 * Cron utils helper module
 *
 * This fie contains useful functions related to cron operations
 */

/**
 * TODO
 *
 * TODO
 */
function __cron_compare($val, $now)
{
    // true case
    if ($val == '*') {
        return true;
    }
    // list of options
    if (strpos($val, ',') !== false) {
        $vals = explode(',', $val);
        foreach ($vals as $val) {
            if (__cron_compare($val, $now)) {
                return true;
            }
        }
        return false;
    }
    // using module
    if (strpos($val, '*/') !== false) {
        $module = intval(substr($val, 2)) ;
        return $now % $module == 0;
    }
    // using range
    if (strpos($val, '-') !== false) {
        $range = explode('-', $val, 2);
        return $now >= intval($range[0]) && $now <= intval($range[1]);
    }
    // direct case
    return intval($val) == $now;
}

/**
 * TODO
 *
 * TODO
 */
function __cron_is_now($minute, $hour, $day, $month, $dow)
{
    $now = getdate();
    return __cron_compare($minute, $now['minutes']) &&
           __cron_compare($hour, $now['hours']) &&
           __cron_compare($day, $now['mday']) &&
           __cron_compare($month, $now['mon']) &&
           __cron_compare($dow, $now['wday']);
}

/**
 * TODO
 *
 * TODO
 */
function __cron_users($arg)
{
    // all users
    if ($arg == '*') {
        return execute_query_array('SELECT login FROM tbl_users WHERE active = 1');
    }
    // list of users
    if (strpos($arg, ',') !== false) {
        return explode(',', $arg);
    }
    // default case
    return [$arg];
}

/**
 * TODO
 *
 * TODO
 */
function cron_gc()
{
    $dir = get_directory('dirs/crondir') ?? getcwd_protected() . '/data/cron/';
    $pids = glob($dir . '*.pid');
    $total = 0;
    foreach ($pids as $file) {
        $temp = unserialize(file_get_contents($file));
        $pid = intval($temp['pid']);
        if (!posix_kill($pid, 0)) {
            $hash = pathinfo($file, PATHINFO_FILENAME);
            $start = date('Y-m-d H:i:s', min(
                filectime($dir . $hash . '.out'),
                filectime($dir . $hash . '.err'),
                filectime($dir . $hash . '.pid'),
                filemtime($dir . $hash . '.out'),
                filemtime($dir . $hash . '.err'),
                filemtime($dir . $hash . '.pid')
            ));
            $stop = date('Y-m-d H:i:s', max(
                filectime($dir . $hash . '.out'),
                filectime($dir . $hash . '.err'),
                filectime($dir . $hash . '.pid'),
                filemtime($dir . $hash . '.out'),
                filemtime($dir . $hash . '.err'),
                filemtime($dir . $hash . '.pid')
            ));
            $out = file_get_contents($dir . $hash . '.out');
            $err = file_get_contents($dir . $hash . '.err');
            $cmd = $temp['cmd'];
            $query = prepare_insert_query('tbl_cron', [
                'cmd' => $cmd,
                'pid' => $pid,
                'out' => $out,
                'err' => $err,
                'start' => $start,
                'stop' => $stop,
            ]);
            db_query(...$query);
            unlink($dir . $hash . '.out');
            unlink($dir . $hash . '.err');
            unlink($dir . $hash . '.pid');
            $total++;
        }
    }
    return $total;
}

/**
 * TODO
 *
 * TODO
 */
function cron_exec()
{
    $dir = get_directory('dirs/crondir') ?? getcwd_protected() . '/data/cron/';
    $tasks = xmlfiles2array(detect_apps_files('xml/cron.xml'));
    $total = 0;
    foreach ($tasks['tasks'] as $task) {
        $task = join_attr_value($task);

        // Check for the cron time execution
        $bool = __cron_is_now(
            $task['minute'] ?? '*',
            $task['hour'] ?? '*',
            $task['day'] ?? '*',
            $task['month'] ?? '*',
            $task['dow'] ?? '*',
        );
        if (!$bool) {
            continue;
        }

        // Prepare the commands that must to be executed
        $cmds = [];
        if (isset($task['cmd'])) {
            $cmds[] = [
                'cmd' => $task['cmd'],
                'user' => $task['user'] ?? '',
            ];
        }
        foreach ($task as $key => $val) {
            if (fix_key($key) != 'task') {
                continue;
            }
            $val = join_attr_value($val);
            if (!isset($val['cmd'])) {
                continue;
            }
            $cmds[] = [
                'cmd' => $val['cmd'],
                'user' => $val['user'] ?? '',
            ];
        }
        if (!count($cmds)) {
            show_php_error(['phperror' => 'Commands not found']);
        }

        // Check for a previous running execution
        $hash = md5(serialize($cmds));
        if (file_exists($dir . $hash . '.pid')) {
            continue;
        }

        // Launch the real commands
        foreach ($cmds as $key => $cmd) {
            $users = __cron_users($cmd['user']);
            $cmd = $cmd['cmd'];
            foreach ($users as $key2 => $user) {
                if ($user) {
                    $users[$key2] = "user=$user php index.php $cmd";
                } else {
                    $users[$key2] = "php index.php $cmd";
                }
            }
            $cmds[$key] = implode(';', $users);
        }
        $cmds = array_map('strval', $cmds);
        $cmds = implode(';', $cmds);
        $out = $dir . $hash . '.out';
        $err = $dir . $hash . '.err';
        $temp = "($cmds) 1>$out 2>$err & echo \$!";
        $pid = intval(ob_passthru($temp));
        file_put_contents($dir . $hash . '.pid', serialize([
            'pid' => $pid,
            'cmd' => $cmds,
        ]));
        chmod_protected($dir . $hash . '.pid', 0666);
        $total++;
    }
    return $total;
}
