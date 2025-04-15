import gzip
import random
from faker import Faker
from pathlib import Path

def generate_app_customers_sql_gz():
    path = Path("app_customers.sql.gz")
    fake = Faker()
    sql = "INSERT INTO `app_customers` (`id`, `active`, `name`, `address`, `city`, `zip`, `country`, `code`, `email`, `phone`, `website`, `notes`, `type_id`) VALUES\n"
    rows = []
    for i in range(1, 101):
        active = random.randint(0, 1)
        name = fake.company().replace("'", "''")
        address = fake.street_address().replace("'", "''")
        city = fake.city()
        zip_code = fake.postcode()
        country = fake.country().replace("'", "''")
        code = f"CUST-{i:04d}"
        email = fake.company_email()
        phone = fake.phone_number()
        website = f"https://{fake.domain_name()}"
        notes = fake.catch_phrase().replace("'", "''")
        type_id = random.randint(1, 3)
        row = f"({i}, {active}, '{name}', '{address}', '{city}', '{zip_code}', '{country}', '{code}', '{email}', '{phone}', '{website}', '{notes}', {type_id})"
        rows.append(row)
    sql += ",\n".join(rows) + ";"
    with gzip.open(path, "wt", encoding="utf-8") as f:
        f.write(sql)
    return path

generate_app_customers_sql_gz()
