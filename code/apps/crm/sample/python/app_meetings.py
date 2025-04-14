import gzip
import random
from faker import Faker
from pathlib import Path

def generate_app_meetings_sql_gz():
    path = Path("app_meetings.sql.gz")
    fake = Faker()
    sql = "INSERT INTO `app_meetings` (`id`, `date`, `title`, `participants_ids`, `related_to`, `content`, `created_by`) VALUES\n"
    rows = []
    for i in range(1, 101):
        date = fake.date_between(start_date='-1y', end_date='today').isoformat()
        title = fake.bs().capitalize().replace("'", "''")
        participants = ",".join(str(random.randint(1, 50)) for _ in range(random.randint(1, 4)))
        related_to = random.randint(1, 100)
        content = fake.paragraph(nb_sentences=3).replace("'", "''")
        created_by = random.randint(1, 5)
        row = f"({i}, '{date}', '{title}', '{participants}', {related_to}, '{content}', {created_by})"
        rows.append(row)
    sql += ",\n".join(rows) + ";"
    with gzip.open(path, "wt", encoding="utf-8") as f:
        f.write(sql)
    return path

generate_app_meetings_sql_gz()
