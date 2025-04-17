import gzip

data = [
    (1, 1, 'Draft', 'Invoice not finalized'),
    (2, 1, 'Issued', 'Invoice has been issued'),
    (3, 1, 'Partially Paid', 'Invoice partially paid'),
    (4, 1, 'Paid', 'Invoice fully paid'),
    (5, 1, 'Overdue', 'Payment is overdue'),
    (6, 1, 'Cancelled', 'Invoice was cancelled'),
    (7, 1, 'Disputed', 'Client raised an issue'),
    (8, 1, 'Refunded', 'Invoice was refunded'),
    (9, 1, 'Archived', 'Archived for historical reference'),
    (10, 1, 'Closed', 'Invoice closed with no further action')
]

with gzip.open('app_invoices_status.sql.gz', 'wt', encoding='utf-8') as f:
    f.write("INSERT INTO app_invoices_status (id, active, name, description) VALUES\n")
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
