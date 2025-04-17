import gzip

data = [
    (1, 1, 'Client', 'Default type for standard clients'),
    (2, 1, 'Distributor', 'Resells our products and services'),
    (3, 1, 'Partner', 'Works closely with us'),
    (4, 1, 'Reseller', 'Authorized to sell our products'),
    (5, 1, 'VIP', 'High priority customer'),
    (6, 1, 'Internal', 'Internal use only'),
    (7, 1, 'Government', 'Public administration customer'),
    (8, 1, 'Education', 'Academic institution'),
    (9, 1, 'Non-Profit', 'Non-commercial entity'),
    (10, 1, 'Other', 'Other unspecified type')
]

with gzip.open('app_customers_types.sql.gz', 'wt', encoding='utf-8') as f:
    f.write("INSERT INTO app_customers_types (id, active, name, description) VALUES\n")
    for i, row in enumerate(data):
        line = "({}, {}, '{}', '{}')".format(
            row[0], row[1],
            row[2].replace("'", "''"),
            row[3].replace("'", "''")
        )
        if i < len(data) - 1:
            line += ","
        f.write(line + "\n")
    f.write(";\n")
