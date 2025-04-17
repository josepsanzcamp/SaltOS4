import gzip

data = [
    (1, 1, 'Good', 'Physical product'),
    (2, 1, 'Service', 'Service provided to customer'),
    (3, 1, 'License', 'Software or intellectual license'),
    (4, 1, 'Subscription', 'Recurring billed item'),
    (5, 1, 'Kit', 'Grouped set of products'),
    (6, 1, 'Digital', 'Downloadable or virtual product'),
    (7, 1, 'Maintenance', 'Post-sale service'),
    (8, 1, 'Training', 'Course or educational service'),
    (9, 1, 'Consulting', 'Professional consulting'),
    (10, 1, 'Other', 'Miscellaneous')
]

with gzip.open('app_products_types.sql.gz', 'wt', encoding='utf-8') as f:
    f.write("INSERT INTO app_products_types (id, active, name, description) VALUES\n")
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
