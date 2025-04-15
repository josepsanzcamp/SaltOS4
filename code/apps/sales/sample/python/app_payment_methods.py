import gzip

# Lista de métodos de pago con sus descripciones
payment_methods = [
    ("Cash", "Payment made in physical currency."),
    ("Credit Card", "Payment made using a credit card."),
    ("Debit Card", "Payment made using a debit card linked to a bank account."),
    ("Bank Transfer", "Payment made via wire transfer or electronic banking."),
    ("PayPal", "Payment made using a PayPal account."),
    ("Cheque", "Payment made using a paper cheque."),
    ("Mobile Payment", "Payment made using a mobile wallet or app."),
    ("Cryptocurrency", "Payment made using Bitcoin or other cryptocurrencies."),
    ("Direct Debit", "Payment directly withdrawn from a bank account."),
    ("Prepaid", "Payment made using prepaid balance or voucher."),
    ("Gift Card", "Payment using a store-issued gift card."),
    ("Other", "Other form of payment not listed above.")
]

default_method = "Bank Transfer"

values = []
for i, (name, description) in enumerate(payment_methods, start=1):
    name_escaped = name.replace("'", "''")
    desc_escaped = description.replace("'", "''")
    is_default = 1 if name == default_method else 0
    values.append(f"({i}, '{name_escaped}', '{desc_escaped}', 1, {is_default})")

sql = (
    "INSERT INTO app_payment_methods "
    "(id, name, description, active, `default`) VALUES\n"
    + ",\n".join(values)
    + ";\n"
)

# Escribir archivo comprimido .sql.gz
with gzip.open("app_payment_methods.sql.gz", "wt", encoding="utf-8") as f:
    f.write(sql)
