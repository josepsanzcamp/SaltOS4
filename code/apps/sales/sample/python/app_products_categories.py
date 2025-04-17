import gzip

data = [
    (1, 1, 'Hardware', 'Physical devices and equipment'),
    (2, 1, 'Software', 'Applications and systems'),
    (3, 1, 'Services', 'Technical or support services'),
    (4, 1, 'Licenses', 'Software or intellectual property licenses'),
    (5, 1, 'Training', 'Courses and educational content'),
    (6, 1, 'Maintenance', 'Post-sale repair or update services'),
    (7, 1, 'Cloud', 'Hosted online services'),
    (8, 1, 'Accessories', 'Complementary items'),
    (9, 1, 'Consumables', 'Items that are used and replaced'),
    (10, 1, 'Other', 'Unclassified category')
]

with gzip.open('app_products_categories.sql.gz', 'wt', encoding='utf-8') as f:
    f.write("INSERT INTO app_products_categories (id, active, name, description) VALUES\n")
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
