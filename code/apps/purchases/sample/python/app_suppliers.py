import gzip
from faker import Faker
from pathlib import Path
import random

def generate_app_suppliers_sql_gz():
    path = Path("app_suppliers.sql.gz")
    fake = Faker()
    sql = "INSERT INTO `app_suppliers` (`id`, `name`, `email`, `phone`, `address`, `city`, `zip`, `country`, `tax_id`, `website`, `notes`, `created_at`, `type_id`) VALUES\n"
    rows = []
    for i in range(1, 101):
        name = fake.company().replace("'", "''")
        email = fake.company_email()
        phone = fake.phone_number()
        address = fake.street_address().replace("'", "''")
        city = fake.city()
        zip_code = fake.postcode()
        country = fake.country().replace("'", "''")
        tax_id = random.randint(1, 4)
        website = f"https://{fake.domain_name()}"
        notes = fake.catch_phrase().replace("'", "''")
        created_at = fake.date_this_decade().isoformat()
        type_id = random.randint(1, 3)
        row = f"({i}, '{name}', '{email}', '{phone}', '{address}', '{city}', '{zip_code}', '{country}', {tax_id}, '{website}', '{notes}', '{created_at}', {type_id})"
        rows.append(row)
    sql += ",\n".join(rows) + ";"
    with gzip.open(path, "wt", encoding="utf-8") as f:
        f.write(sql)
    return path

generate_app_suppliers_sql_gz()
