import gzip

data = [
    (1, 1, 'Draft', 'Quote in preparation'),
    (2, 1, 'Sent', 'Quote sent to customer'),
    (3, 1, 'Accepted', 'Customer accepted the quote'),
    (4, 1, 'Rejected', 'Customer rejected the quote'),
    (5, 1, 'Expired', 'Quote validity expired'),
    (6, 1, 'Cancelled', 'Manually cancelled'),
    (7, 1, 'Pending', 'Awaiting response'),
    (8, 1, 'Reviewed', 'Reviewed by manager'),
    (9, 1, 'Converted', 'Converted to invoice'),
    (10, 1, 'Closed', 'Closed without result')
]

with gzip.open('app_quotes_status.sql.gz', 'wt', encoding='utf-8') as f:
    f.write("INSERT INTO app_quotes_status (id, active, name, description) VALUES\n")
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
