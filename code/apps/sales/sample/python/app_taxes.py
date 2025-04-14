import gzip
from pathlib import Path

def generate_app_taxes_sql_gz():
    path = Path("app_taxes.sql.gz")
    tax_rows = [
        (1, "IVA 21%", 21.00, 1, 1),
        (2, "IVA 10%", 10.00, 1, 0),
        (3, "IVA 4%", 4.00, 1, 0),
        (4, "Exento / No sujeto", 0.00, 1, 0)
    ]
    sql = "INSERT INTO `app_taxes` (`id`, `name`, `value`, `active`, `default`) VALUES\n"
    sql += ",\n".join(
        f"({r[0]}, '{r[1]}', {r[2]}, {r[3]}, {r[4]})" for r in tax_rows
    ) + ";"
    with gzip.open(path, "wt", encoding="utf-8") as f:
        f.write(sql)
    return path

generate_app_taxes_sql_gz()
