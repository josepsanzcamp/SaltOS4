import gzip
import random
from faker import Faker
from pathlib import Path

def generar_cif():
    letras = "ABCDEFGHJNPQRSUVW"
    letra = random.choice(letras)
    numero = random.randint(1000000, 9999999)
    digito = random.randint(0, 9)
    return f"{letra}{numero}{digito}"

def generate_app_customers_sql_gz():
    path = Path("app_customers.sql.gz")
    fake = Faker()
    sql = "INSERT INTO `app_customers` (`id`, `active`, `name`, `address`, `city`, `province`, `zip`, `country`, `code`, `email`, `phone`, `website`, `notes`, `type_id`) VALUES\n"
    rows = []
    for i in range(1, 101):
        active = random.randint(0, 1)
        name = fake.company().replace("'", "''")
        address = fake.street_address().replace("'", "''")
        city = fake.city()
        province = fake.state()
        zip_code = fake.postcode()
        country = fake.country().replace("'", "''")
        code = generar_cif()
        email = fake.company_email()
        phone = fake.phone_number()
        website = f"https://{fake.domain_name()}"
        notes = fake.catch_phrase().replace("'", "''")
        type_id = random.randint(1, 3)
        row = f"({i}, {active}, '{name}', '{address}', '{city}', '{province}', '{zip_code}', '{country}', '{code}', '{email}', '{phone}', '{website}', '{notes}', {type_id})"
        rows.append(row)
    sql += ",\n".join(rows) + ";"
    with gzip.open(path, "wt", encoding="utf-8") as f:
        f.write(sql)
    return path

generate_app_customers_sql_gz()
