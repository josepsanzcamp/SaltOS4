#!/usr/bin/env python3
import os
import sys
import argparse

# Rutas base
ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), ".."))
APPS_PATH = os.path.join(ROOT, "code", "apps")
API_PATH = os.path.join(ROOT, "code", "api")

# Argumentos
parser = argparse.ArgumentParser(description="Move translation keys between groups and the global dictionary")
parser.add_argument("--from", dest="from_sources", required=True, help="Comma-separated list of source groups or 'global'")
parser.add_argument("--to", dest="to_targets", required=True, help="Comma-separated list of destination groups or 'global'")
parser.add_argument("--items", required=True, help="Comma-separated list of translation keys to move")
args = parser.parse_args()

from_sources = [x.strip() for x in args.from_sources.split(",") if x.strip()]
to_targets = [x.strip() for x in args.to_targets.split(",") if x.strip()]
items = [k.strip() for k in args.items.split(",") if k.strip()]

if not items:
    print("No items provided.")
    sys.exit(1)

# Detectar todos los idiomas disponibles (en los grupos de origen si no es global)
lang_dirs = set()
if "global" in from_sources:
    api_locale_path = os.path.join(API_PATH, "locale")
    lang_dirs = {d for d in os.listdir(api_locale_path) if os.path.isdir(os.path.join(api_locale_path, d))}
else:
    for group in from_sources:
        group_locale_dir = os.path.join(APPS_PATH, group, "locale")
        if os.path.isdir(group_locale_dir):
            langs = [d for d in os.listdir(group_locale_dir) if os.path.isdir(os.path.join(group_locale_dir, d))]
            lang_dirs.update(langs)

for lang in sorted(lang_dirs):
    # Construir contenido por idioma
    source_files = {}
    for src in from_sources:
        if src == "global":
            path = os.path.join(API_PATH, "locale", lang, "messages.yaml")
        else:
            path = os.path.join(APPS_PATH, src, "locale", lang, "messages.yaml")
        if os.path.isfile(path):
            with open(path, encoding="utf-8") as f:
                source_files[src] = f.readlines()

    # Buscar claves en los orígenes
    lines_to_move = []
    for src, lines in source_files.items():
        remaining = []
        for line in lines:
            line_stripped = line.lstrip()
            if any(line_stripped.startswith(f"{key}:") for key in items):
                lines_to_move.append(line)
            else:
                remaining.append(line)
        # Guardar archivo fuente actualizado sin las líneas movidas
        with open(os.path.join(API_PATH if src == "global" else os.path.join(APPS_PATH, src),
                               "locale", lang, "messages.yaml"), "w", encoding="utf-8") as f:
            f.writelines(remaining)

    if not lines_to_move:
        print(f"[{lang}] No keys found to move")
        continue

    # Añadir a cada destino
    for tgt in to_targets:
        if tgt == "global":
            dest_path = os.path.join(API_PATH, "locale", lang, "messages.yaml")
        else:
            dest_path = os.path.join(APPS_PATH, tgt, "locale", lang, "messages.yaml")
        os.makedirs(os.path.dirname(dest_path), exist_ok=True)
        if os.path.isfile(dest_path):
            with open(dest_path, "r", encoding="utf-8") as f:
                dest_lines = f.readlines()
        else:
            dest_lines = []

        # Asegurar que termina con salto de línea limpio
        if dest_lines and not dest_lines[-1].endswith("\n"):
            dest_lines[-1] += "\n"

        dest_lines.extend(lines_to_move)

        with open(dest_path, "w", encoding="utf-8") as f:
            f.writelines(dest_lines)

        print(f"[{lang}] Moved {len(lines_to_move)} keys to {tgt}")
