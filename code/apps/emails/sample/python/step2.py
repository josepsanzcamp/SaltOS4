import os
import random
import gzip
import shutil
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from email.mime.image import MIMEImage
from email.mime.application import MIMEApplication
from email.utils import format_datetime
import datetime

# Fix to prevent the base64 in all mime contents
from email import charset
charset.add_charset('utf-8', charset.SHORTEST, charset.QP)

# === Directorios ===
image_directory = 'generated_images'
pdf_directory = 'generated_pdfs'
gzip_directory = 'emails_gzip'
os.makedirs(gzip_directory, exist_ok=True)

# === Datos base ===
personal_messages = [
    "Hey! Just checking in. Been a while since we last talked. Hope you're doing well!",
    "I saw your post yesterday and it reminded me of our trip to the mountains. Good times!",
    "Let me know if you're free for a coffee this weekend. I’d love to catch up properly."
]
business_messages = [
    "Please find the attached report with all the project updates from this week.",
    "Let me know your availability to schedule the next planning meeting.",
    "Attached is the revised proposal for the client. Please review before our call."
]

def generate_realistic_paragraph():
    samples = personal_messages + business_messages
    return " ".join(random.choices(samples, k=random.randint(10, 20))) + "."

# === Crear email ===
def create_email_with_attachments(index):
    msg = MIMEMultipart("alternative")

    # Cabeceras
    if index % 2 == 0:
        sender = f"{random.choice(['alice', 'bob'])}@example.com"
        recipient = f"{random.choice(['diana', 'eve'])}@example.com"
        subject = "Catching Up"
    else:
        sender = f"{random.choice(['jane.doe', 'manager'])}@business.com"
        recipient = f"{random.choice(['ceo', 'it.support'])}@business.com"
        subject = "Business Update"

    msg['From'] = sender
    msg['To'] = recipient
    msg['Subject'] = subject

    base_date = datetime.datetime(2024, 1, 1, 8, 0, 0)
    email_date = base_date + datetime.timedelta(minutes=10 * index)
    msg['Date'] = format_datetime(email_date)

    # Cuerpo (sin codificación forzada, charset=utf-8)
    n_paragraphs = random.randint(1, 6)
    body_plain = "\n\n".join(generate_realistic_paragraph() for _ in range(n_paragraphs))
    body_html = "<html><body>" + "".join(f"<p>{generate_realistic_paragraph()}</p>" for _ in range(n_paragraphs)) + "</body></html>"

    part_plain = MIMEText(body_plain, "plain", _charset="utf-8")
    part_html = MIMEText(body_html, "html", _charset="utf-8")

    msg.attach(part_plain)
    msg.attach(part_html)

    # Adjuntos (se codifican en base64 automáticamente)
    if random.choice([True, False]):
        if random.choice([True, False]):
            img_file = random.choice(os.listdir(image_directory))
            with open(os.path.join(image_directory, img_file), "rb") as f:
                img = MIMEImage(f.read())
                img.add_header('Content-Disposition', 'attachment', filename=img_file)
                msg.attach(img)
        else:
            pdf_file = random.choice(os.listdir(pdf_directory))
            with open(os.path.join(pdf_directory, pdf_file), "rb") as f:
                pdf = MIMEApplication(f.read(), _subtype="pdf")
                pdf.add_header('Content-Disposition', 'attachment', filename=pdf_file)
                msg.attach(pdf)

    # Guardar como .eml y comprimir
    eml_path = os.path.join(gzip_directory, f"email_{index:04d}.eml")
    with open(eml_path, "wb") as f:
        f.write(msg.as_bytes())

    with open(eml_path, "rb") as f_in:
        with gzip.open(eml_path + ".gz", "wb") as f_out:
            shutil.copyfileobj(f_in, f_out)

    os.remove(eml_path)

# === Ejecutar ===
for i in range(1, 101):
    create_email_with_attachments(i)
