import gzip
import random
from faker import Faker
from pathlib import Path
from datetime import date as dt_date

def generate_app_purchase_sql_gz():
    path = Path("app_purchase.sql.gz")
    fake = Faker()
    sql = "INSERT INTO `app_purchase` (`id`, `date`, `supplier_id`, `code`, `description`, `subtotal`, `tax`, `total`, `paid`, `status_id`, `invoice_date`, `paid_date`, `notes`) VALUES\n"
    rows = []
    for i in range(1, 101):
        date_obj = fake.date_between(start_date='-6M', end_date='-1d')
        date = date_obj.isoformat()
        supplier_id = random.randint(1, 100)
        code = f"PO-{i:04d}"
        description = fake.sentence(nb_words=6).replace("'", "''")
        subtotal = round(random.uniform(100, 3000), 2)
        tax = round(subtotal * 0.21, 2)
        total = round(subtotal + tax, 2)
        paid = round(random.uniform(0, total), 2)
        status = random.randint(1, 4)
        invoice_date = fake.date_between(start_date=date_obj, end_date='+10d').isoformat()
        invoice_date_obj = dt_date.fromisoformat(invoice_date)
        paid_date = fake.date_between(start_date=invoice_date_obj, end_date='+30d').isoformat()
        notes = fake.text(max_nb_chars=60).replace("'", "''")
        row = f"({i}, '{date}', {supplier_id}, '{code}', '{description}', {subtotal}, {tax}, {total}, {paid}, {status}, '{invoice_date}', '{paid_date}', '{notes}')"
        rows.append(row)
    sql += ",\n".join(rows) + ";"
    with gzip.open(path, "wt", encoding="utf-8") as f:
        f.write(sql)
    return path

generate_app_purchase_sql_gz()
