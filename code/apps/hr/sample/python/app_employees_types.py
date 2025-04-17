import gzip

data = [
    (1, 1, 'Internal', 'Employee on company payroll'),
    (2, 1, 'Freelance', 'Independent contractor'),
    (3, 1, 'External', 'Works for a third-party provider'),
    (4, 1, 'Temporary', 'Short-term contract employee'),
    (5, 1, 'Intern', 'Student or junior under training'),
    (6, 1, 'Part-time', 'Limited hours per week'),
    (7, 1, 'Full-time', 'Standard full-time employee'),
    (8, 1, 'Consultant', 'Advisor or expert hired for projects'),
    (9, 1, 'Seasonal', 'Hired during peak periods'),
    (10, 1, 'Other', 'Other type of employee')
]

with gzip.open('app_employees_types.sql.gz', 'wt', encoding='utf-8') as f:
    f.write("INSERT INTO app_employees_types (id, active, name, description) VALUES\n")
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
