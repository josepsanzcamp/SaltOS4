import gzip
import random
from faker import Faker
from pathlib import Path

def generate_app_workorders_sql_gz():
    path = Path("app_workorders.sql.gz")
    fake = Faker()
    sql = "INSERT INTO `app_workorders` (`id`, `date`, `worker_id`, `client_id`, `description`, `hours`, `price`, `total`, `invoice_id`) VALUES\n"
    rows = []
    for i in range(1, 101):
        date = fake.date_between(start_date='-6M', end_date='today').isoformat()
        worker_id = random.randint(1, 50)
        client_id = random.randint(1, 100)
        description = fake.sentence(nb_words=8).replace("'", "''")
        hours = round(random.uniform(1, 8), 2)
        price = round(random.uniform(20, 100), 2)
        total = round(hours * price, 2)
        invoice_id = random.randint(1, 100)
        row = f"({i}, '{date}', {worker_id}, {client_id}, '{description}', {hours}, {price}, {total}, {invoice_id})"
        rows.append(row)
    sql += ",\n".join(rows) + ";"
    with gzip.open(path, "wt", encoding="utf-8") as f:
        f.write(sql)
    return path

generate_app_workorders_sql_gz()
