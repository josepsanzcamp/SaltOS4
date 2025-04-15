import gzip
from pathlib import Path

def generate_app_taxes_sql_gz():
    path = Path("app_taxes.sql.gz")
    tax_rows = [
        (1, "VAT 21%", "Standard VAT rate of 21%", 21.00, 1, 1),
        (2, "VAT 10%", "Reduced VAT rate of 10%", 10.00, 1, 0),
        (3, "VAT 4%", "Super-reduced VAT rate of 4%", 4.00, 1, 0),
        (4, "Exempt / Not subject", "Operations exempt or not subject to VAT", 0.00, 1, 0)
    ]
    sql = "INSERT INTO `app_taxes` (`id`, `name`, `description`, `value`, `active`, `default`) VALUES\n"
    values = []
    for r in tax_rows:
        id_, name, desc, value, active, default = r
        name_escaped = name.replace("'", "''")
        desc_escaped = desc.replace("'", "''")
        values.append(f"({id_}, '{name_escaped}', '{desc_escaped}', {value}, {active}, {default})")
    sql += ",\n".join(values) + ";\n"
    with gzip.open(path, "wt", encoding="utf-8") as f:
        f.write(sql)
    return path

generate_app_taxes_sql_gz()
