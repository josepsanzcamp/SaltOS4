import gzip

record = (
    1,
    1,
    'SaltOS Solutions SL',
    'B12345678',
    'Calle Ficticia 123, 3ºA',
    'Barcelona',
    'Barcelona',
    '08001',
    'Spain',
    '+34 933 123 456',
    'info@saltos.org',
    'https://www.saltos.org',
    'ES76 1234 5678 9101 2345 6789',
    'BESMESMMXXX',
    'RE - Régimen General',
    '8299',
    'Entidad acogida al régimen general del IVA'
)

with gzip.open('app_company.sql.gz', 'wt', encoding='utf-8') as f:
    f.write("INSERT INTO app_company (\n")
    f.write("    id, active, name, code, address, city, province, zip, country,\n")
    f.write("    phone, email, website, iban, swift,\n")
    f.write("    fiscal_regime, activity_code, notes\n")
    f.write(") VALUES\n")

    values = "({}, {}, '{}', '{}', '{}', '{}', '{}', '{}', '{}', '{}', '{}', '{}', '{}', '{}', '{}', '{}', '{}');".format(
        record[0],
        record[1],
        record[2].replace("'", "''"),
        record[3].replace("'", "''"),
        record[4].replace("'", "''"),
        record[5].replace("'", "''"),
        record[6].replace("'", "''"),
        record[7].replace("'", "''"),
        record[8].replace("'", "''"),
        record[9].replace("'", "''"),
        record[10].replace("'", "''"),
        record[11].replace("'", "''"),
        record[12].replace("'", "''"),
        record[13].replace("'", "''"),
        record[14].replace("'", "''"),
        record[15].replace("'", "''"),
        record[16].replace("'", "''")
    )

    f.write(values + "\n")
