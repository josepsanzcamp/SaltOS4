import gzip
import random
from faker import Faker
from pathlib import Path

def generate_app_leads_sql_gz():
    path = Path("app_leads.sql.gz")
    fake = Faker()
    sql = "INSERT INTO `app_leads` (`id`, `name`, `email`, `phone`, `company`, `source`, `status_id`, `assigned_to`, `notes`, `created_at`) VALUES\n"
    rows = []
    for i in range(1, 101):
        name = fake.name().replace("'", "''")
        email = fake.email()
        phone = fake.phone_number()
        company = fake.company().replace("'", "''")
        source = fake.random_element(elements=("Web", "Referral", "Event", "Email", "Phone")).replace("'", "''")
        status = random.randint(1, 4)
        assigned_to = random.randint(1, 5)
        notes = fake.sentence(nb_words=10).replace("'", "''")
        created_at = fake.date_between(start_date='-1y', end_date='today').isoformat()
        row = f"({i}, '{name}', '{email}', '{phone}', '{company}', '{source}', {status}, {assigned_to}, '{notes}', '{created_at}')"
        rows.append(row)
    sql += ",\n".join(rows) + ";"
    with gzip.open(path, "wt", encoding="utf-8") as f:
        f.write(sql)
    return path

generate_app_leads_sql_gz()
