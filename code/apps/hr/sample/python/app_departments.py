import gzip
from faker import Faker
from pathlib import Path
import random

def generate_app_departments_sql_gz():
    path = Path("app_departments.sql.gz")
    fake = Faker()
    sql = "INSERT INTO `app_departments` (`id`, `active`, `name`, `code`, `parent_id`, `notes`) VALUES\n"
    rows = []
    for i in range(1, 101):
        active = random.randint(0, 1);
        name = fake.job().replace("'", "''")
        code = f"DPT-{i:04d}"
        parent_id = random.choice([0] + list(range(1, i))) if i > 1 else 0
        notes = fake.sentence(nb_words=6).replace("'", "''")
        row = f"({i}, {active}, '{name}', '{code}', {parent_id}, '{notes}')"
        rows.append(row)
    sql += ",\n".join(rows) + ";"
    with gzip.open(path, "wt", encoding="utf-8") as f:
        f.write(sql)
    return path

generate_app_departments_sql_gz()
