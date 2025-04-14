import gzip
import random
from faker import Faker
from pathlib import Path

def generate_app_products_sql_gz():
    path = Path("app_products.sql.gz")
    fake = Faker()
    sql = "INSERT INTO `app_products` (`id`, `name`, `code`, `description`, `price`, `tax_id`, `type_id`, `active`) VALUES\n"
    rows = []
    for i in range(1, 101):
        name = fake.catch_phrase().replace("'", "''")
        code = f"PRD-{i:04d}"
        description = fake.text(max_nb_chars=100).replace("'", "''")
        price = round(random.uniform(10, 500), 2)
        tax_id = random.randint(1, 4)
        type_id = random.randint(1, 3)
        active = 1
        row = f"({i}, '{name}', '{code}', '{description}', {price}, {tax_id}, {type_id}, {active})"
        rows.append(row)
    sql += ",\n".join(rows) + ";"
    with gzip.open(path, "wt", encoding="utf-8") as f:
        f.write(sql)
    return path

generate_app_products_sql_gz()
