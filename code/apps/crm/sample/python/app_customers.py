import gzip
import random
from faker import Faker
from pathlib import Path

def generate_app_customers_sql_gz():
    path = Path("app_customers.sql.gz")
    fake = Faker()
    sql = "INSERT INTO `app_customers` (`id`, `code`, `name`, `address`, `city`, `zip`, `country`, `email`, `phone`, `website`, `notes`, `created_at`, `type_id`) VALUES\n"
    rows = []
    for i in range(1, 101):
        code = f"CUST-{i:04d}"
        name = fake.company().replace("'", "''")
        address = fake.street_address().replace("'", "''")
        city = fake.city()
        zip_code = fake.postcode()
        country = fake.country().replace("'", "''")
        email = fake.company_email()
        phone = fake.phone_number()
        website = f"https://{fake.domain_name()}"
        notes = fake.catch_phrase().replace("'", "''")
        created_at = fake.date_this_decade().isoformat()
        type_id = random.randint(1, 3)
        row = f"({i}, '{code}', '{name}', '{address}', '{city}', '{zip_code}', '{country}', '{email}', '{phone}', '{website}', '{notes}', '{created_at}', {type_id})"
        rows.append(row)
    sql += ",\n".join(rows) + ";"
    with gzip.open(path, "wt", encoding="utf-8") as f:
        f.write(sql)
    return path

generate_app_customers_sql_gz()
