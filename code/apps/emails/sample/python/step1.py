from fpdf import FPDF
from PIL import Image, ImageDraw, ImageFont
import os

# Crear carpetas para imágenes y PDFs
image_directory = 'generated_images'
pdf_directory = 'generated_pdfs'
os.makedirs(image_directory, exist_ok=True)
os.makedirs(pdf_directory, exist_ok=True)

# Crear imágenes
def create_image_with_text(text, filename):
    img = Image.new('RGB', (200, 100), color=(73, 109, 137))
    d = ImageDraw.Draw(img)
    try:
        font = ImageFont.truetype("arial.ttf", 15)
    except IOError:
        font = ImageFont.load_default()
    x = 10
    y = 40
    d.text((x, y), text, font=font, fill=(255, 255, 0))
    img.save(os.path.join(image_directory, filename))

for i in range(5):
    create_image_with_text(f'Image {i+1}', f'image_{i+1}.jpg')

# Crear PDFs
def create_pdf_with_text(filename):
    pdf = FPDF()
    pdf.add_page()
    pdf.set_font("Arial", size=12)
    text = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. " * 10
    for _ in range(5):
        pdf.multi_cell(0, 10, text)
    pdf.output(os.path.join(pdf_directory, filename))

for i in range(5):
    create_pdf_with_text(f'document_{i+1}.pdf')
