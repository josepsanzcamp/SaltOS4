import os
import random
import gzip
from datetime import timedelta
from faker import Faker

fake = Faker()
random.seed(42)
Faker.seed(42)

# --- Definición de impuestos ---
taxes = [
    {"id": 1, "name": "IVA 21%", "value": 21.00},
    {"id": 2, "name": "IVA 10%", "value": 10.00},
    {"id": 3, "name": "IVA 4%",  "value": 4.00},
    {"id": 4, "name": "Exempt / Not subject", "value": 0.00},
]

# --- Parámetros iniciales ---
n_invoices = 100
invoice_rows = []
line_rows = []
tax_rows = []

invoice_id_seq = 1
line_id_seq = 1
tax_id_seq = 1

# --- Funciones auxiliares ---
def gen_invoice_code(prefix, year, number):
    return f"{prefix}{year}-{number:04d}"

def escape_sql_text(text):
    return text.replace("'", "''")

def safe_sql_str(s):
    return f"'{escape_sql_text(s)}'" if s else "''"

def safe_sql_date(d):
    return f"'{d.strftime('%Y-%m-%d')}'" if d else "'0000-00-00'"

def random_discount():
    return random.choices([0, 5, 10, 15, 20, 25], weights=[70, 10, 8, 6, 4, 2])[0]

def save_sql_gz(table_name, values, output_dir="."):
    sql = f"INSERT INTO app_{table_name} VALUES\n" + ",\n".join(values) + ";\n"
    path = os.path.join(output_dir, f"app_{table_name}.sql.gz")
    with gzip.open(path, "wt", encoding="utf-8") as f:
        f.write(sql)
    return path

def generar_cif():
    letras = "ABCDEFGHJNPQRSUVW"
    letra = random.choice(letras)
    numero = random.randint(1000000, 9999999)
    digito = random.randint(0, 9)
    return f"{letra}{numero}{digito}"

# --- Generador de datos ---
for i in range(n_invoices):
    year = 2025
    is_closed = random.choice([0, 1])
    is_paid = random.choice([0, 1]) if is_closed else 0

    proforma_code = gen_invoice_code("P", year, i + 1)
    proforma_date = fake.date_between(start_date='-60d', end_date='today')
    invoice_code = gen_invoice_code("F", year, i + 1) if is_closed else ""
    invoice_date = fake.date_between(start_date=proforma_date, end_date='today') if is_closed else None
    due_date = invoice_date + timedelta(days=random.choice([15, 30, 45])) if is_closed else None
    paid_date = fake.date_between(start_date=invoice_date, end_date='today') if is_paid and invoice_date else None
    payment_method_id = random.randint(1, 12)
    status_id = random.randint(1, 5)

    company_id = 1
    company_name = 'SaltOS Solutions SL'
    company_code = 'B12345678'
    company_address = 'Calle Ficticia 123, 3ºA'
    company_city = 'Barcelona'
    company_province = 'Barcelona'
    company_zip = '08001'
    company_country = 'Spain'

    customer_name = escape_sql_text(fake.company())
    customer_address = escape_sql_text(fake.address().replace("\n", ", "))
    customer_city = escape_sql_text(fake.city())
    customer_province = fake.state()
    customer_zip = escape_sql_text(fake.postcode())
    customer_country = escape_sql_text(fake.country())
    customer_code = generar_cif()
    customer_id = random.randint(1, 50)
    description = escape_sql_text(fake.paragraph())

    n_lines = int(random.random() ** 2 * 49) + 1
    subtotal = 0
    tax_buckets = {}

    for _ in range(n_lines):
        quantity = round(random.uniform(1, 10), 2)
        price = round(random.uniform(10, 200), 2)
        discount = random_discount()
        tax = random.choice(taxes)
        base_total = round(quantity * price * (1 - discount / 100), 2)

        line_rows.append(f"({line_id_seq},{invoice_id_seq},0,"
                         f"{safe_sql_str(fake.bs())},{quantity},{price},{discount},"
                         f"{tax['id']},{tax['value']},{base_total})")
        line_id_seq += 1
        subtotal += base_total

        if tax['id'] not in tax_buckets:
            tax_buckets[tax['id']] = {"tax_name": tax["name"], "tax_value": tax["value"], "base": 0.0}
        tax_buckets[tax['id']]["base"] += base_total

    total_tax = 0.0
    for tax_id, data in tax_buckets.items():
        base = round(data["base"], 2)
        tax_amount = round(base * data["tax_value"] / 100, 2)
        total_tax += tax_amount
        tax_rows.append(f"({tax_id_seq},{invoice_id_seq},{tax_id},'{escape_sql_text(data['tax_name'])}',"
                        f"{data['tax_value']},{base},{tax_amount})")
        tax_id_seq += 1

    total = round(subtotal + total_tax, 2)
    paid = total if is_paid else 0.0

    invoice_rows.append(
        f"({invoice_id_seq},"
        f"'{proforma_code}',"
        f"{safe_sql_date(proforma_date)},"
        f"{safe_sql_str(invoice_code)},"
        f"{safe_sql_date(invoice_date)},"
        f"{company_id},'{company_name}','{company_address}','{company_city}',"
        f"'{company_province}','{company_zip}','{company_country}','{company_code}',"
        f"{customer_id},'{customer_name}','{customer_address}','{customer_city}',"
        f"'{customer_province}','{customer_zip}','{customer_country}','{customer_code}',"
        f"'{description}',{subtotal},{total_tax},{total},"
        f"{payment_method_id},{safe_sql_date(due_date)},{paid},{safe_sql_date(paid_date)},"
        f"{status_id},{is_closed},{is_paid})"
    )
    invoice_id_seq += 1

# --- Guardar ficheros ---
save_sql_gz("invoices", invoice_rows)
save_sql_gz("invoices_lines", line_rows)
save_sql_gz("invoices_taxes", tax_rows)
