import gzip

data = [
    (1, 1, 'Manufacturer', 'Produces goods directly'),
    (2, 1, 'Wholesaler', 'Sells large quantities to resellers'),
    (3, 1, 'Distributor', 'Distributes products regionally'),
    (4, 1, 'Transporter', 'Logistics and shipping provider'),
    (5, 1, 'Freelancer', 'Individual supplier or technician'),
    (6, 1, 'Agency', 'Acts on behalf of other suppliers'),
    (7, 1, 'Service Provider', 'Offers support or maintenance'),
    (8, 1, 'Partner', 'Strategic or long-term provider'),
    (9, 1, 'Government', 'Public sector entity'),
    (10, 1, 'Other', 'Unspecified type of supplier')
]

with gzip.open('app_suppliers_types.sql.gz', 'wt', encoding='utf-8') as f:
    f.write("INSERT INTO app_suppliers_types (id, active, name, description) VALUES\n")
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
