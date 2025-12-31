<?php

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable Generic.Files.LineLength

$source = 'path/to/old_database';
$destination = 'path/to/user/data/files/saltos.sqlite';
$path = 'path/to/user/files/';

function dump_query_as_insert($query, $dest_table, $out)
{
    // Guardamos el query
    file_put_contents('/tmp/query.in', $query);

    fwrite($out, "DELETE FROM $dest_table;\n");
    fwrite($out, "DELETE FROM sqlite_sequence WHERE name='$dest_table';\n");
    fwrite($out, "BEGIN;\n");

    // mysql devuelve columnas separadas por tabs
    global $source;
    $cmd = "cat /tmp/query.in | sqlite3 -json $source";
    $stream = popen($cmd, 'r');

    $expected_cols  = null;

    while (!feof($stream)) {
        $line = fgets($stream);
        if ($line === false) {
            break;
        }

        $line = rtrim($line, "\r\n");
        $line = ltrim($line, '[');
        $line = rtrim($line, ',');
        $line = rtrim($line, ']');
        $fields = json_decode($line, true);

        // Si no tenemos aún la estructura, contamos columnas
        if ($expected_cols === null) {
            $expected_cols = count($fields);
        }

        $count_fields = count($fields);
        if ($count_fields === $expected_cols) {
            // Escapamos para SQLite
            $fields = array_map(function ($v) {
                $v = strval($v);
                $v = stripcslashes($v);
                $v = str_replace("'", "''", $v);
                $v = str_replace(["\00a5", "\00a0"], '', $v);
                return "'$v'";
            }, $fields);

            $sql = "INSERT INTO $dest_table VALUES (" . implode(',', $fields) . ");\n";
            fwrite($out, $sql);
        } else {
            echo "internal error with $dest_table!!!\n";
            echo $line;
            //~ die();
        }
    }

    fwrite($out, "COMMIT;\n");
    pclose($stream);
}

function db_query($query)
{
    file_put_contents('/tmp/query.in', $query);
    global $source;
    $cmd = "cat /tmp/query.in | sqlite3 -json $source";
    ob_start();
    passthru($cmd);
    $buffer = ob_get_clean();
    return $buffer;
}

$queries = [
    // Main data from main apps
    'app_emails' => 'SELECT * FROM tbl_correo',
    'app_emails_address' => 'SELECT * FROM tbl_correo_a',
    'app_emails_deletes' => 'SELECT * FROM tbl_correo_d',
    'tbl_users' => "SELECT id, activo, id_grupo, login, 'TODO' q, 'TODO' b, hora_ini, hora_fin, dias_sem, '' FROM tbl_usuarios",
    'tbl_groups' => "SELECT id, '1', nombre, nombre a, descripcion, '' FROM tbl_grupos",
    'tbl_users_passwords' => "SELECT id, activo, id a, DATETIME('now', 'localtime'), '' b, '' c, password, DATETIME('now', 'localtime', '+1 year') FROM tbl_usuarios",

    // Index data from main apps
    'app_emails_index' => 'SELECT * FROM idx_correo',
    'tbl_users_index' => 'SELECT * FROM idx_usuarios',
    'tbl_groups_index' => 'SELECT * FROM idx_grupos',

    // Control data from main apps
    'app_emails_control' => "
        SELECT id_registro, id_usuario, (SELECT id_grupo FROM tbl_usuarios WHERE tbl_registros.id_usuario=tbl_usuarios.id), datetime, '' a, '' b
        FROM tbl_registros WHERE id_aplicacion=(SELECT id FROM tbl_aplicaciones WHERE codigo='correo') AND first=1",
    'tbl_users_control' => "
        SELECT id_registro, id_usuario, (SELECT id_grupo FROM tbl_usuarios WHERE tbl_registros.id_usuario=tbl_usuarios.id), datetime, '' a, '' b
        FROM tbl_registros WHERE id_aplicacion=(SELECT id FROM tbl_aplicaciones WHERE codigo='usuarios') AND first=1",
    'tbl_groups_control' => "
        SELECT id_registro, id_usuario, (SELECT id_grupo FROM tbl_usuarios WHERE tbl_registros.id_usuario=tbl_usuarios.id), datetime, '' a, '' b
        FROM tbl_registros WHERE id_aplicacion=(SELECT id FROM tbl_aplicaciones WHERE codigo='grupos') AND first=1",

    // Files data from main apps
    'app_emails_files' => "
        SELECT id, id_usuario, datetime, id_registro, '', fichero, fichero_size, fichero_type, fichero_file, fichero_hash, search, indexed, retries
        FROM tbl_ficheros WHERE id_aplicacion=(SELECT id FROM tbl_aplicaciones WHERE codigo='correo')",
    'tbl_users_files' => "
        SELECT id, id_usuario, datetime, id_registro, '', fichero, fichero_size, fichero_type, fichero_file, fichero_hash, search, indexed, retries
        FROM tbl_ficheros WHERE id_aplicacion=(SELECT id FROM tbl_aplicaciones WHERE codigo='usuarios')",
    'tbl_groups_files' => "
        SELECT id, id_usuario, datetime, id_registro, '', fichero, fichero_size, fichero_type, fichero_file, fichero_hash, search, indexed, retries
        FROM tbl_ficheros WHERE id_aplicacion=(SELECT id FROM tbl_aplicaciones WHERE codigo='grupos')",

    // Notes data from main apps
    'tbl_users_notes' => "
        SELECT id, id_usuario, datetime, id_registro, comentarios
        FROM tbl_comentarios WHERE id_aplicacion=(SELECT id FROM tbl_aplicaciones WHERE codigo='usuarios')",
    'tbl_groups_notes' => "
        SELECT id, id_usuario, datetime, id_registro, comentarios
        FROM tbl_comentarios WHERE id_aplicacion=(SELECT id FROM tbl_aplicaciones WHERE codigo='grupos')",

    // Accounts emails
    'app_emails_accounts' => '
        SELECT id, id_usuario, email_name, email_from, email_signature_file,
            pop3_host, pop3_port, pop3_extra, pop3_user, pop3_pass, pop3_delete, pop3_days,
            smtp_host, smtp_port, smtp_extra, smtp_user, smtp_pass,
            email_disabled, email_privated, email_default, email_addmetocc, email_crt
        FROM tbl_usuarios_c',
];

foreach ($queries as $table => $query) {
    echo "Exporting $table ... ";
    $out = fopen("migrate_$table.sql", 'w');
    dump_query_as_insert($query, $table, $out);
    fclose($out);
    echo "OK\n";
}

$files = [
    'app_emails_accounts' => ['email_signature', 'SELECT id, email_signature_file FROM tbl_usuarios_c'],
];

foreach ($files as $table => $temp) {
    [$field, $query] = $temp;
    echo "Fixing $table ... ";
    $rows = db_query($query);
    $rows = json_decode($rows, true);
    foreach ($rows as $row) {
        $row = array_values($row);
        [$id, $file] = $row;
        if (!file_exists($path . $file)) {
            continue;
        }
        if (!is_file($path . $file)) {
            continue;
        }
        $buffer = file_get_contents($path . $file);
        $buffer = addslashes($buffer);
        $buffer = str_replace("'", "''", $buffer);
        $sql = "UPDATE $table SET $field='$buffer' WHERE id=$id;\n";
        $out = fopen("migrate_$table.sql", 'a');
        fwrite($out, $sql);
        fclose($out);
    }
    echo "OK\n";
}

foreach ($queries as $table => $query) {
    echo "Importing $table ... ";
    passthru("sqlite3 $destination < migrate_$table.sql");
    echo "OK\n";
}
