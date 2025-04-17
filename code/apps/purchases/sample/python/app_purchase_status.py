import gzip

data = [
    (1, 1, 'Draft', 'Purchase not yet confirmed'),
    (2, 1, 'Ordered', 'Order has been placed with supplier'),
    (3, 1, 'Received', 'Goods or services have been received'),
    (4, 1, 'Invoiced', 'Invoice has been received'),
    (5, 1, 'Paid', 'Payment completed'),
    (6, 1, 'Partially Paid', 'Partial payment made'),
    (7, 1, 'Cancelled', 'Order was cancelled'),
    (8, 1, 'Returned', 'Goods returned to supplier'),
    (9, 1, 'Archived', 'Marked for historical reference'),
    (10, 1, 'Other', 'Other status')
]

with gzip.open('app_purchase_status.sql.gz', 'wt', encoding='utf-8') as f:
    f.write("INSERT INTO app_purchase_status (id, active, name, description) VALUES\n")
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
