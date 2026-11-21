import gzip

data = [
    (1, 1, '2026-11-02 09:15:00', 'success', 'Customer saved successfully', 1793607300),
    (2, 1, '2026-11-02 09:15:30', 'event', 'saltos.emails.update', 1793607330),
    (3, 1, '2026-11-02 10:42:10', 'danger', 'Could not connect to the mail server', 1793612530),
    (4, 1, '2026-11-03 08:05:00', 'event', 'saltos.emails.folders', 1793689500),
    (5, 1, '2026-11-03 12:30:45', 'success', 'Invoice sent by email', 1793705445),
    (6, 1, '2026-11-04 16:20:00', 'event', 'saltos.emails.update', 1793805600),
    (7, 1, '2026-11-05 11:11:11', 'success', 'Backup completed', 1793873471),
    (8, 1, '2026-11-06 09:00:00', 'danger', 'The certificate expires in 7 days', 1793952000),
    (9, 1, '2026-11-06 17:45:20', 'event', 'saltos.emails.update', 1793983520),
    (10, 1, '2026-11-07 13:10:05', 'success', 'Quote accepted by the customer', 1794053405)
]

with gzip.open('tbl_push.sql.gz', 'wt', encoding='utf-8') as f:
    f.write("INSERT INTO tbl_push (id, user_id, datetime, type, message, timestamp) VALUES\n")
    for i, row in enumerate(data):
        line = "({}, {}, '{}', '{}', '{}', {})".format(
            row[0], row[1], row[2], row[3],
            row[4].replace("'", "''"),
            row[5]
        )
        if i < len(data) - 1:
            line += ","
        f.write(line + "\n")
    f.write(";\n")
