import gzip
import random
from faker import Faker
from pathlib import Path
from datetime import timedelta

def generate_app_meetings_sql_gz():
    path = Path("app_meetings.sql.gz")
    fake = Faker()
    sql = (
        "INSERT INTO `app_meetings` "
        "(`id`, `start_time`, `end_time`, `title`, `location`, `participants`, "
        "`agenda`, `topics_approved`, `topics_rejected`, `topics_pending`, `customer_id`) VALUES\n"
    )
    rows = []

    for i in range(1, 101):
        start_dt = fake.date_time_between(start_date='-1y', end_date='now')
        duration_minutes = random.choice([30, 45, 60, 90, 120])
        end_dt = start_dt + timedelta(minutes=duration_minutes)

        start_time = start_dt.strftime("%Y-%m-%d %H:%M:%S")
        end_time = end_dt.strftime("%Y-%m-%d %H:%M:%S")
        title = fake.catch_phrase().replace("'", "''")
        location = fake.city().replace("'", "''")

        participants = "\n".join(
            fake.name().replace("'", "''")
            for _ in range(random.randint(2, 5))
        )

        def fake_paragraphs(min_paragraphs=1, max_paragraphs=4):
            return "\n\n".join(
                fake.paragraph().replace("'", "''")
                for _ in range(random.randint(min_paragraphs, max_paragraphs))
            )

        agenda = fake_paragraphs()
        topics_approved = fake_paragraphs()
        topics_rejected = fake_paragraphs()
        topics_pending = fake_paragraphs()
        customer_id = random.randint(1, 100)

        row = (
            f"({i}, '{start_time}', '{end_time}', '{title}', '{location}', "
            f"'{participants}', '{agenda}', '{topics_approved}', "
            f"'{topics_rejected}', '{topics_pending}', {customer_id})"
        )
        rows.append(row)

    sql += ",\n".join(rows) + ";\n"

    with gzip.open(path, "wt", encoding="utf-8") as f:
        f.write(sql)

    return path

generate_app_meetings_sql_gz()
