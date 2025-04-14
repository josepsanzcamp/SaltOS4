import gzip
import random
from faker import Faker
from pathlib import Path
from datetime import date as dt_date

def generate_app_employees_sql_gz():
    path = Path("app_employees.sql.gz")
    fake = Faker()
    sql = "INSERT INTO `app_employees` (`id`, `name`, `email`, `phone`, `department_id`, `job_title`, `start_date`, `end_date`, `type_id`, `notes`, `user_id`) VALUES\n"
    rows = []
    for i in range(1, 101):
        name = fake.name().replace("'", "''")
        email = fake.email()
        phone = fake.phone_number()
        department_id = random.randint(1, 20)
        job_title = fake.job().replace("'", "''")
        start_date_obj = fake.date_between(start_date='-5y', end_date='-1y')
        start_date = start_date_obj.isoformat()
        if random.random() > 0.1:
            end_date = "'0000-00-00'"
        else:
            end_date_val = fake.date_between(start_date=start_date_obj, end_date='today').isoformat()
            end_date = f"'{end_date_val}'"
        type_id = random.randint(1, 3)
        notes = fake.sentence(nb_words=8).replace("'", "''")
        user_id = random.randint(1, 20)
        row = f"({i}, '{name}', '{email}', '{phone}', {department_id}, '{job_title}', '{start_date}', {end_date}, {type_id}, '{notes}', {user_id})"
        rows.append(row)
    sql += ",\n".join(rows) + ";"
    with gzip.open(path, "wt", encoding="utf-8") as f:
        f.write(sql)
    return path

generate_app_employees_sql_gz()
