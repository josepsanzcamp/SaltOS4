#!/usr/bin/env python3
import os
import sys
import xml.etree.ElementTree as ET
import re
import yaml
from glob import glob
import argparse
import csv
from collections import defaultdict

TRANSLATABLE_ATTRS = {
    "label", "tooltip", "placeholder",
    "title", "subtitle", "close", "body", "footer"
}

ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), ".."))
APPS_PATH = os.path.join(ROOT, "code", "apps")
API_LOCALE_PATH = os.path.join(ROOT, "code", "api", "locale")

parser = argparse.ArgumentParser(description="Check translations usage and duplicates")
parser.add_argument("--lang", help="Language to analyze, e.g., ca_ES")
parser.add_argument("--filter", choices=["missing", "present", "missing_but_in_other_group"], help="Filter output")
parser.add_argument("--group", help="Limit analysis to a single app group")
parser.add_argument("--csv", help="Export result to CSV file")
parser.add_argument("--strict", action="store_true", help="Report duplicates between groups, not just global")
args = parser.parse_args()

LANG = args.lang
FILTER = args.filter
GROUP_FILTER = args.group
STRICT = args.strict

def text_to_key(text):
    return re.sub(r'[^a-z0-9]+', '_', text.strip().lower()).strip('_')

def count_key_occurrences_in_file(filepath, key):
    count = 0
    key_pattern = re.compile(rf'^{re.escape(key)}\s*:\s+')
    with open(filepath, encoding="utf-8") as f:
        for line in f:
            if key_pattern.match(line.strip()):
                count += 1
    return count

def find_duplicates():
    print("# Checking for duplicate translation keys across files")
    all_data = defaultdict(lambda: defaultdict(list))

    for lang in os.listdir(API_LOCALE_PATH):
        global_path = os.path.join(API_LOCALE_PATH, lang, "messages.yaml")
        if os.path.isfile(global_path):
            with open(global_path, encoding="utf-8") as f:
                data = yaml.safe_load(f) or {}
                for k in data:
                    all_data[lang][k].append("global")
                    occurrences = count_key_occurrences_in_file(global_path, k)
                    if occurrences > 1:
                        print(f"[{lang}] Duplicate key '{k}' found {occurrences} times in global")

    for group in os.listdir(APPS_PATH):
        loc_path = os.path.join(APPS_PATH, group, "locale")
        if not os.path.isdir(loc_path):
            continue
        for lang in os.listdir(loc_path):
            yaml_path = os.path.join(loc_path, lang, "messages.yaml")
            if os.path.isfile(yaml_path):
                with open(yaml_path, encoding="utf-8") as f:
                    data = yaml.safe_load(f) or {}
                    for k in data:
                        all_data[lang][k].append(group)
                        occurrences = count_key_occurrences_in_file(yaml_path, k)
                        if occurrences > 1:
                            print(f"[{lang}] Duplicate key '{k}' found {occurrences} times in group '{group}'")

    for lang, keymap in sorted(all_data.items()):
        for key, places in keymap.items():
            if len(places) > 1:
                if "global" in places and any(p != "global" for p in places):
                    print(f"[{lang}] Key '{key}' defined in global and also in: {', '.join(p for p in places if p != 'global')}")
                elif STRICT:
                    print(f"[{lang}] Key '{key}' repeated in: {', '.join(places)}")

if not LANG:
    find_duplicates()

    print("# Checking for missing keys across languages in each group")

    def collect_keys_by_lang(base_path):
        result = {}
        for lang in os.listdir(base_path):
            path = os.path.join(base_path, lang, "messages.yaml")
            if os.path.isfile(path):
                with open(path, encoding="utf-8") as f:
                    data = yaml.safe_load(f) or {}
                    result[lang] = set(data.keys())
        return result

    # Global
    global_keys = collect_keys_by_lang(API_LOCALE_PATH)
    all_global_keys = set().union(*global_keys.values())
    for lang in sorted(global_keys):
        missing = all_global_keys - global_keys[lang]
        for k in sorted(missing):
            print(f"[global] Key '{k}' missing in: {lang}")

    # Por grupo
    for group in sorted(os.listdir(APPS_PATH)):
        locale_path = os.path.join(APPS_PATH, group, "locale")
        if not os.path.isdir(locale_path):
            continue
        group_keys = collect_keys_by_lang(locale_path)
        if not group_keys:
            continue
        all_group_keys = set().union(*group_keys.values())
        for lang in sorted(group_keys):
            missing = all_group_keys - group_keys[lang]
            for k in sorted(missing):
                print(f"[{group}] Key '{k}' missing in: {lang}")

    sys.exit(0)

# ---------------------------
# Análisis por idioma activo
# ---------------------------

def load_group_messages_yaml(group):
    path = os.path.join(APPS_PATH, group, "locale", LANG, "messages.yaml")
    if os.path.isfile(path):
        with open(path, encoding="utf-8") as f:
            return yaml.safe_load(f) or {}
    return {}

def load_generic_messages():
    path = os.path.join(API_LOCALE_PATH, LANG, "messages.yaml")
    if os.path.isfile(path):
        with open(path, encoding="utf-8") as f:
            return yaml.safe_load(f) or {}
    return {}

def load_all_other_groups_messages(current_group):
    others = {}
    for group in os.listdir(APPS_PATH):
        if group == current_group:
            continue
        path = os.path.join(APPS_PATH, group, "locale", LANG, "messages.yaml")
        if os.path.isfile(path):
            with open(path, encoding="utf-8") as f:
                others[group] = yaml.safe_load(f) or {}
    return others

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
                    m = re.search(r'"label"\s*:\s*"(.*?)"', obj)
                    if m:
                        keys.add((f"{side}.label", m.group(1)))
                elif obj.startswith("{") and 'value' in obj:
                    m = re.search(r'"value"\s*:\s*"(.*?)"', obj)
                    if m:
                        keys.add((f"{side}.value", m.group(1)))
                else:
                    keys.add((side, obj))
    if 'actions' in node.attrib:
        for m in re.finditer(r'"(label|tooltip)"\s*:\s*"(.*?)"', node.attrib['actions']):
            keys.add((f"actions.{m.group(1)}", m.group(2)))
    if 'menu' in node.attrib:
        for m in re.finditer(r'"label"\s*:\s*"(.*?)"', node.attrib['menu']):
            keys.add(("menu.label", m.group(1)))
    return keys

def extract_keys_pdf(xml_path):
    keys = set()
    try:
        tree = ET.parse(xml_path)
        root = tree.getroot()
        for node in root.iter():
            if node.tag in {"text", "textarea", "output", "query"} and node.text:
                matches = re.findall(r'\bT\(("|\')(.*?)\1\)', node.text, re.DOTALL)
                for _, match in matches:
                    if match.strip():
                        keys.add(("pdf", match.strip()))
    except ET.ParseError:
        print(f"[ERROR] {os.path.basename(xml_path)} Error parsing PDF XML")
    return keys

def extract_keys_yaml(path):
    keys = set()
    try:
        with open(path, encoding="utf-8") as f:
            data = yaml.safe_load(f)
            for section in ("list", "form"):
                if section in data:
                    for entry in data[section]:
                        if isinstance(entry, list) and len(entry) >= 3:
                            keys.add((section, entry[2]))
    except Exception:
        print(f"[ERROR] {os.path.basename(path)} Error parsing YAML")
    return keys

def extract_keys_js(path):
    keys = set()
    try:
        with open(path, encoding="utf-8") as f:
            content = f.read()
            for _, match in re.findall(r'\bT\(("|\')(.*?)\1\)', content, re.DOTALL):
                if match.strip():
                    keys.add(("js", match.strip()))
    except Exception:
        print(f"[ERROR] {os.path.basename(path)} Error reading JS")
    return keys

def extract_keys_manifest(path):
    keys = set()
    try:
        tree = ET.parse(path)
        root = tree.getroot()
        for tag in root.iter():
            if tag.tag in {"group", "app"}:
                for attr in ("name", "description"):
                    val = tag.attrib.get(attr)
                    if val:
                        keys.add(("manifest", val))
    except ET.ParseError:
        print(f"[ERROR] {os.path.basename(path)} Error parsing manifest.xml")
    return keys

# ----------- Análisis por idioma -----------

generic_messages = load_generic_messages()
other_groups_messages = load_all_other_groups_messages(GROUP_FILTER) if GROUP_FILTER else {}

results = []
print(f"# Translation check for language: {LANG}\n")
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
                    print(f"[ERROR] {group} {base} XML parse error")
                    continue
            for origin, text in entries:
                key = text_to_key(text)
                missing = key not in combined_messages
                found_elsewhere = any(key in m for g, m in other_groups_messages.items()) if missing else False
                if ((FILTER == "missing" and not missing) or
                    (FILTER == "present" and missing) or
                    (FILTER == "missing_but_in_other_group" and (not missing or not found_elsewhere))):
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
                if ((FILTER == "missing" and not missing) or
                    (FILTER == "present" and missing) or
                    (FILTER == "missing_but_in_other_group" and (not missing or not found_elsewhere))):
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
                if ((FILTER == "missing" and not missing) or
                    (FILTER == "present" and missing) or
                    (FILTER == "missing_but_in_other_group" and (not missing or not found_elsewhere))):
                    continue
                status = "missing" if missing else "present"
                if missing and found_elsewhere:
                    status = "missing_but_in_other_group"
                results.append([group, base, origin, text[:35], key, status])
                print("{:<10} {:<20} {:<10} {:<40} {:<40} {:<30}".format(group, base, origin, text[:35], key, status))

if args.csv:
    with open(args.csv, "w", newline="", encoding="utf-8") as f:
        writer = csv.writer(f)
        writer.writerow(["grupo", "archivo", "origen", "original", "key", "estado"])
        writer.writerows(results)
