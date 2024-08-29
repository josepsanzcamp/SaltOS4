<?php

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable Generic.Files.LineLength

$source = 'old_database';
$destination = 'new_database';
$path = 'path/to/user/files/';

function db_query($query)
{
    file_put_contents('/tmp/query', $query);
    ob_start();
    passthru('cat /tmp/query | mysql -N');
    $buffer = ob_get_clean();
    $buffer = trim($buffer);
    return $buffer;
}

function rows_parser($rows)
{
    $rows = explode("\n", $rows);
    foreach ($rows as $key => $val) {
        $rows[$key] = explode("\t", $val);
    }
    return $rows;
}

$queries = [
    // Main data from main apps
    'app_customers' => "INSERT INTO $destination.app_customers SELECT * FROM $source.tbl_clientes",
    'app_invoices' => "INSERT INTO $destination.app_invoices SELECT * FROM $source.tbl_facturas",
    'app_invoices_concepts' => "INSERT INTO $destination.app_invoices_concepts SELECT * FROM $source.tbl_facturas_c",
    'app_invoices_expirations' => "INSERT INTO $destination.app_invoices_expirations SELECT * FROM $source.tbl_facturas_v",
    'app_emails' => "INSERT INTO $destination.app_emails SELECT * FROM $source.tbl_correo",
    'app_emails_address' => "INSERT INTO $destination.app_emails_address SELECT * FROM $source.tbl_correo_a",
    'app_emails_deletes' => "INSERT INTO $destination.app_emails_deletes SELECT * FROM $source.tbl_correo_d",

    // Index data from main apps
    'app_customers_index' => "INSERT INTO $destination.app_customers_index SELECT * FROM $source.idx_clientes",
    'app_invoices_index' => "INSERT INTO $destination.app_invoices_index SELECT * FROM $source.idx_facturas",
    'app_emails_index' => "INSERT INTO $destination.app_emails_index SELECT * FROM $source.idx_correo",

    // Control data from main apps
    'app_customers_control' => "INSERT INTO $destination.app_customers_control
        SELECT id_registro, id_usuario, (SELECT id_grupo FROM $source.tbl_usuarios WHERE $source.tbl_registros.id_usuario=$source.tbl_usuarios.id), datetime, '', ''
        FROM $source.tbl_registros WHERE id_aplicacion=(SELECT id FROM $source.tbl_aplicaciones WHERE codigo='clientes') AND first=1",
    'app_invoices_control' => "INSERT INTO $destination.app_invoices_control
        SELECT id_registro, id_usuario, (SELECT id_grupo FROM $source.tbl_usuarios WHERE $source.tbl_registros.id_usuario=$source.tbl_usuarios.id), datetime, '', ''
        FROM $source.tbl_registros WHERE id_aplicacion=(SELECT id FROM $source.tbl_aplicaciones WHERE codigo='facturas') AND first=1",
    'app_emails_control' => "INSERT INTO $destination.app_emails_control
        SELECT id_registro, id_usuario, (SELECT id_grupo FROM $source.tbl_usuarios WHERE $source.tbl_registros.id_usuario=$source.tbl_usuarios.id), datetime, '', ''
        FROM $source.tbl_registros WHERE id_aplicacion=(SELECT id FROM $source.tbl_aplicaciones WHERE codigo='correo') AND first=1",

    // Files data from main apps
    'app_customers_files' => "INSERT INTO $destination.app_customers_files
        SELECT id, id_usuario, datetime, id_registro, '', fichero, fichero_size, fichero_type, fichero_file, fichero_hash, search, indexed, retries
        FROM $source.tbl_ficheros WHERE id_aplicacion=(SELECT id FROM $source.tbl_aplicaciones WHERE codigo='clientes')",
    'app_invoices_files' => "INSERT INTO $destination.app_invoices_files
        SELECT id, id_usuario, datetime, id_registro, '', fichero, fichero_size, fichero_type, fichero_file, fichero_hash, search, indexed, retries
        FROM $source.tbl_ficheros WHERE id_aplicacion=(SELECT id FROM $source.tbl_aplicaciones WHERE codigo='clientes')",
    'app_emails_files' => "INSERT INTO $destination.app_emails_files
        SELECT id, id_usuario, datetime, id_registro, '', fichero, fichero_size, fichero_type, fichero_file, fichero_hash, search, indexed, retries
        FROM $source.tbl_ficheros WHERE id_aplicacion=(SELECT id FROM $source.tbl_aplicaciones WHERE codigo='correo')",

    // Accounts emails
    'app_emails_accounts' => "INSERT INTO $destination.app_emails_accounts
        SELECT id, id_usuario, email_name, email_from, email_signature_file,
            pop3_host, pop3_port, pop3_extra, pop3_user, pop3_pass, pop3_delete, pop3_days,
            smtp_host, smtp_port, smtp_extra, smtp_user, smtp_pass,
            email_disabled, email_privated, email_default, email_addmetocc, email_crt
        FROM $source.tbl_usuarios_c",
];

foreach ($queries as $table => $query) {
    $temp = "SELECT COUNT(*) FROM $destination.$table";
    $exists = db_query($temp);
    if ($exists) {
        continue;
    }
    db_query($query);
}

$files = [
    'app_emails_accounts' => 'email_signature',
];

foreach ($files as $table => $field) {
    $query = "SELECT id, $field FROM $destination.$table";
    $rows = db_query($query);
    $rows = rows_parser($rows);
    foreach ($rows as $row) {
        [$id, $file] = $row;
        if (!file_exists($path . $file)) {
            continue;
        }
        if (!is_file($path . $file)) {
            continue;
        }
        $buffer = file_get_contents($path . $file);
        $buffer = addslashes($buffer);
        $query = "UPDATE $destination.$table SET $field='$buffer' WHERE id=$id";
        db_query($query);
    }
}
