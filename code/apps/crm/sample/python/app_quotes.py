import gzip
import random
from faker import Faker
from pathlib import Path
from datetime import date as dt_date

def generate_app_quotes_sql_gz():
    path = Path("app_quotes.sql.gz")
    fake = Faker()
    sql = "INSERT INTO `app_quotes` (`id`, `date`, `customer_id`, `title`, `description`, `subtotal`, `tax`, `total`, `status_id`, `valid_until`, `created_by`) VALUES\n"
    rows = []
    for i in range(1, 101):
        date_obj = fake.date_between(start_date='-6M', end_date='today')
        date_str = date_obj.isoformat()
        customer_id = random.randint(1, 100)
        title = fake.catch_phrase().replace("'", "''")
        description = fake.text(max_nb_chars=100).replace("'", "''")
        subtotal = round(random.uniform(100, 3000), 2)
        tax = round(subtotal * 0.21, 2)
        total = round(subtotal + tax, 2)
        status = random.randint(1, 4)
        valid_until = fake.date_between(start_date=date_obj, end_date='+30d').isoformat()
        created_by = random.randint(1, 5)
        row = f"({i}, '{date_str}', {customer_id}, '{title}', '{description}', {subtotal}, {tax}, {total}, {status}, '{valid_until}', {created_by})"
        rows.append(row)
    sql += ",\n".join(rows) + ";"
    with gzip.open(path, "wt", encoding="utf-8") as f:
        f.write(sql)
    return path

generate_app_quotes_sql_gz()
