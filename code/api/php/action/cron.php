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
 * Garbage Collector action
 *
 * This action executes the gc_exec function in the gc.php library, the execution
 * of this accion only is allowed from the command line
 */

if (!get_data('server/xuid')) {
    show_php_error(['phperror' => 'Permission denied']);
}

if (!semaphore_acquire('cron')) {
    show_php_error(['phperror' => 'Could not acquire the semaphore']);
}

db_connect();
$tasks = xmlfiles2array(detect_apps_files('xml/cron.xml'));
require_once 'php/lib/cron.php';
foreach ($tasks['tasks'] as $task) {
    $task = join_attr_value($task);
    $bool = __cron_is_now(
        $task['minute'] ?? '*',
        $task['hour'] ?? '*',
        $task['day'] ?? '*',
        $task['month'] ?? '*',
        $task['dow'] ?? '*',
    );
    if (!$bool) {
        //~ continue;
    }
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
    foreach ($cmds as $key => $cmd) {
        $users = __cron_users($cmd['user']);
        foreach ($users as $key2 => $user) {
            $users[$key2] = "user=$user php index.php {$cmd['cmd']}";
        }
        $cmds[$key] = implode(';', $users);
    }
    $cmds = implode(';', $cmds);
    $out = get_temp_file('.out');
    $err = get_temp_file('.err');
    $cmds = "($cmds) 1>$out 2>$err & echo \$!";
    $pid = ob_passthru($cmds);
    print_r([$pid, $out, $err]);
}

output_handler_json([
    'status' => 'ok',
    'datetime' => current_datetime(),
]);
