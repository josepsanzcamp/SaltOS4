#!/usr/bin/env python3
import os
import sys
import xml.etree.ElementTree as ET
import re
import yaml
from glob import glob

# Atributos traducibles directos
TRANSLATABLE_ATTRS = {
    "label", "tooltip", "placeholder",
    "title", "subtitle", "close", "body", "footer"
}

# Rutas base
ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), ".."))
APPS_PATH = os.path.join(ROOT, "code", "apps")
API_LOCALE_PATH = os.path.join(ROOT, "code", "api", "locale")

# Leer argumentos
import argparse
parser = argparse.ArgumentParser(description="Check missing translations")
parser.add_argument("lang", help="Idioma, por ejemplo ca_ES")
parser.add_argument("--filter", choices=["missing", "present"], help="Filtrar solo claves faltantes o presentes")
parser.add_argument("--group", help="Analizar solo un grupo de apps (crm, emails, sales, etc)")
parser.add_argument("--csv", help="Ruta del archivo CSV de salida")
args = parser.parse_args()

LANG = args.lang
FILTER = args.filter
GROUP_FILTER = args.group

# Normalizador de texto a clave
def text_to_key(text):
    return re.sub(r'[^a-z0-9]+', '_', text.strip().lower()).strip('_')

# Cargar YAML de traducción de un grupo
def load_group_messages_yaml(group):
    path = os.path.join(APPS_PATH, group, "locale", LANG, "messages.yaml")
    if os.path.isfile(path):
        with open(path, encoding="utf-8") as f:
            return yaml.safe_load(f) or {}
    return {}

# Cargar mensajes genéricos del sistema
def load_generic_messages():
    gen_path = os.path.join(API_LOCALE_PATH, LANG, "messages.yaml")
    if os.path.isfile(gen_path):
        with open(gen_path, encoding="utf-8") as f:
            return yaml.safe_load(f) or {}
    return {}

# Cargar todos los mensajes de todos los grupos (excepto el actual)
def load_all_other_groups_messages(current_group):
    others = {}
    for group in os.listdir(APPS_PATH):
        if group == current_group:
            continue
        group_path = os.path.join(APPS_PATH, group, "locale", LANG, "messages.yaml")
        if os.path.isfile(group_path):
            with open(group_path, encoding="utf-8") as f:
                others[group] = yaml.safe_load(f) or {}
    return others

# Extraer claves desde un nodo XML clásico
def extract_keys_xml(node):
    keys = set()
    for attr in TRANSLATABLE_ATTRS:
        value = node.attrib.get(attr)
        if value:
            keys.add((attr, value))

    if node.attrib.get("type") == "table":
        for side in ("header", "footer"):
            obj = node.attrib.get(side)
            if obj:
                if obj.startswith("{") and 'label' in obj:
                    label_match = re.search(r'"label"\s*:\s*"(.*?)"', obj)
                    if label_match:
                        keys.add((f"{side}.label", label_match.group(1)))
                elif obj.startswith("{") and 'value' in obj:
                    value_match = re.search(r'"value"\s*:\s*"(.*?)"', obj)
                    if value_match:
                        keys.add((f"{side}.value", value_match.group(1)))
                else:
                    keys.add((side, obj))

    if 'actions' in node.attrib:
        for match in re.finditer(r'"(label|tooltip)"\s*:\s*"(.*?)"', node.attrib['actions']):
            keys.add((f"actions.{match.group(1)}", match.group(2)))

    if 'menu' in node.attrib:
        for match in re.finditer(r'"label"\s*:\s*"(.*?)"', node.attrib['menu']):
            keys.add(("menu.label", match.group(1)))

    return keys

def extract_keys_pdf(xml_path):
    keys = set()
    try:
        tree = ET.parse(xml_path)
        root = tree.getroot()
        for node in root.iter():
            if node.tag in {"text", "textarea", "output", "query"} and node.text:
                matches = re.findall(r'\bT\((["\'])(.*?)\1\)', node.text, re.DOTALL)
                for _, match in matches:
                    if match.strip():
                        keys.add(("pdf", match.strip()))
    except ET.ParseError:
        print(f"[ERROR]\t{os.path.basename(xml_path)}\tError de parseo PDF XML")
    return keys

def extract_keys_yaml(yaml_path):
    keys = set()
    try:
        with open(yaml_path, encoding="utf-8") as f:
            data = yaml.safe_load(f)
            for section in ("list", "form"):
                if section in data:
                    for entry in data[section]:
                        if isinstance(entry, list) and len(entry) >= 3:
                            keys.add((section, entry[2]))
    except Exception:
        print(f"[ERROR]\t{os.path.basename(yaml_path)}\tError de parseo YAML")
    return keys

def extract_keys_js(js_path):
    keys = set()
    try:
        with open(js_path, encoding="utf-8") as f:
            content = f.read()
            matches = re.findall(r'\bT\((["\'])(.*?)\1\)', content, re.DOTALL)
            for _, match in matches:
                if match.strip():
                    keys.add(("js", match.strip()))
    except Exception:
        print(f"[ERROR]\t{os.path.basename(js_path)}\tError de lectura JS")
    return keys

def extract_keys_manifest(manifest_path):
    keys = set()
    try:
        tree = ET.parse(manifest_path)
        root = tree.getroot()
        for tag in root.iter():
            if tag.tag in {"group", "app"}:
                for attr in ("name", "description"):
                    val = tag.attrib.get(attr)
                    if val:
                        keys.add(("manifest", val))
    except ET.ParseError:
        print(f"[ERROR]\t{os.path.basename(manifest_path)}\tError de parseo manifest.xml")
    return keys

# Cargar mensajes genéricos y de otros grupos
generic_messages = load_generic_messages()
other_groups_messages = load_all_other_groups_messages(GROUP_FILTER) if GROUP_FILTER else {}

results = []
print(f"# Revisión de traducciones para el idioma: {LANG}\n")
print("{:<10} {:<20} {:<10} {:<40} {:<40} {:<30}".format("grupo", "archivo", "origen", "original", "key", "estado"))
print("{:-<10} {:-<20} {:-<10} {:-<40} {:-<40} {:-<30}".format("", "", "", "", "", ""))

for group in os.listdir(APPS_PATH):
    if GROUP_FILTER and group != GROUP_FILTER:
        continue

    group_path = os.path.join(APPS_PATH, group)
    if not os.path.isdir(group_path):
        continue

    group_messages = load_group_messages_yaml(group)
    combined_messages = {**generic_messages, **group_messages}

    xml_dir = os.path.join(group_path, "xml")
    js_dir = os.path.join(group_path, "js")

    if os.path.isdir(xml_dir):
        for xml_file in glob(os.path.join(xml_dir, "*.xml")):
            base = os.path.basename(xml_file)
            if base == "manifest.xml":
                entries = extract_keys_manifest(xml_file)
            elif base.endswith("_pdf.xml"):
                entries = extract_keys_pdf(xml_file)
            else:
                try:
                    tree = ET.parse(xml_file)
                    root = tree.getroot()
                    entries = set()
                    for elem in root.iter():
                        entries.update(extract_keys_xml(elem))
                except ET.ParseError:
                    print(f"[ERROR]\t{group}\t{base}\tError de parseo XML normal")
                    continue

            for origin, text in entries:
                key = text_to_key(text)
                missing = key not in combined_messages
                found_elsewhere = any(key in m for g, m in other_groups_messages.items()) if missing else False
                if (FILTER == "missing" and not missing) or (FILTER == "present" and missing):
                    continue
                status = "missing" if missing else "present"
                if missing and found_elsewhere:
                    status = "missing_but_in_other_group"
                results.append([group, base, origin, text[:35], key, status])
                print("{:<10} {:<20} {:<10} {:<40} {:<40} {:<30}".format(group, base, origin, text[:35], key, status))

        for yaml_file in glob(os.path.join(xml_dir, "*.yaml")):
            base = os.path.basename(yaml_file)
            entries = extract_keys_yaml(yaml_file)
            for origin, text in entries:
                key = text_to_key(text)
                missing = key not in combined_messages
                found_elsewhere = any(key in m for g, m in other_groups_messages.items()) if missing else False
                if (FILTER == "missing" and not missing) or (FILTER == "present" and missing):
                    continue
                status = "missing" if missing else "present"
                if missing and found_elsewhere:
                    status = "missing_but_in_other_group"
                results.append([group, base, origin, text[:35], key, status])
                print("{:<10} {:<20} {:<10} {:<40} {:<40} {:<30}".format(group, base, origin, text[:35], key, status))

    if os.path.isdir(js_dir):
        for js_file in glob(os.path.join(js_dir, "*.js")):
            if js_file.endswith(".min.js") or js_file.endswith(".map"):
                continue
            base = os.path.basename(js_file)
            entries = extract_keys_js(js_file)
            for origin, text in entries:
                key = text_to_key(text)
                missing = key not in combined_messages
                found_elsewhere = any(key in m for g, m in other_groups_messages.items()) if missing else False
                if (FILTER == "missing" and not missing) or (FILTER == "present" and missing):
                    continue
                status = "missing" if missing else "present"
                if missing and found_elsewhere:
                    status = "missing_but_in_other_group"
                results.append([group, base, origin, text[:35], key, status])
                print("{:<10} {:<20} {:<10} {:<40} {:<40} {:<30}".format(group, base, origin, text[:35], key, status))

if args.csv:
    import csv
    with open(args.csv, "w", newline="", encoding="utf-8") as f:
        writer = csv.writer(f)
        writer.writerow(["grupo", "archivo", "origen", "original", "key", "estado"])
        writer.writerows(results)
