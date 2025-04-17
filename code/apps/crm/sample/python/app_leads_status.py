import gzip

data = [
    (1, 1, 'New', 'Lead just created'),
    (2, 1, 'Contacted', 'Initial contact made'),
    (3, 1, 'Qualified', 'Qualified lead'),
    (4, 1, 'Proposal Sent', 'Proposal has been delivered'),
    (5, 1, 'Negotiation', 'Under negotiation'),
    (6, 1, 'Won', 'Deal closed successfully'),
    (7, 1, 'Lost', 'Lead lost'),
    (8, 1, 'Archived', 'No longer active'),
    (9, 1, 'Recycled', 'Revived from previous loss'),
    (10, 1, 'Unreachable', 'Could not be contacted')
]

with gzip.open('app_leads_status.sql.gz', 'wt', encoding='utf-8') as f:
    f.write("INSERT INTO app_leads_status (id, active, name, description) VALUES\n")
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
