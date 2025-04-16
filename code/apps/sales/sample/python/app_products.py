import gzip
import random
from faker import Faker
from pathlib import Path

def generate_app_products_sql_gz():
    path = Path("app_products.sql.gz")
    fake = Faker()
    sql = "INSERT INTO `app_products` (`id`, `name`, `code`, `description`, `price`, `tax_id`, `type_id`, `active`, `unit`, `cost`, `margin`, `barcode`, `category_id`, `brand`, `model`, `stock`, `stock_min`, `stock_max`, `location`, `image_url`) VALUES\n"

    rows = []
    for i in range(1, 101):
        name = fake.catch_phrase().replace("'", "''")
        code = f"PRD-{i:04d}"
        description = fake.text(max_nb_chars=100).replace("'", "''")

        unit = random.choice(["unidad", "kg", "h", "m²", "paquete"])
        cost = round(random.uniform(5, 300), 2)
        margin = round(random.uniform(5, 40), 2)  # en porcentaje
        barcode = f"{random.randint(1000000000000, 9999999999999)}"

        category_id = random.randint(1, 5)  # se asume que hay 5 categorías en app_types
        brand = fake.company().replace("'", "''")
        model = fake.bothify(text="MOD-####-??").upper()

        stock = round(random.uniform(0, 500), 2)
        stock_min = round(random.uniform(0, 50), 2)
        stock_max = round(stock + random.uniform(10, 200), 2)
        location = fake.lexify(text="Almacén ??? - Estantería ??").replace("'", "''")

        image_url = f"https://cdn.example.com/products/{i:04d}.jpg"

        price = round(cost * (1 + margin / 100), 2)
        # ~ price = round(random.uniform(10, 500), 2)
        tax_id = random.randint(1, 4)
        type_id = random.randint(1, 3)
        active = 1
        row = f"({i}, '{name}', '{code}', '{description}', {price}, {tax_id}, {type_id}, {active}, '{unit}', {cost}, {margin}, '{barcode}', {category_id}, '{brand}', '{model}', {stock}, {stock_min}, {stock_max}, '{location}', '{image_url}')"
        rows.append(row)
    sql += ",\n".join(rows) + ";"
    with gzip.open(path, "wt", encoding="utf-8") as f:
        f.write(sql)
    return path

generate_app_products_sql_gz()
