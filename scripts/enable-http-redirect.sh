#!/usr/bin/env bash
# Habilita redirección HTTP→HTTPS en Apache (puerto 80) hacia Caddy (443).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SITE="libroclases-redirect"
SRC="$ROOT/config/apache/${SITE}.conf"
DEST="/etc/apache2/sites-available/${SITE}.conf"

if [[ $EUID -ne 0 ]]; then
  echo "Este script requiere sudo. Ejecuta:"
  echo "  sudo $0"
  exit 1
fi

if [[ ! -f "$SRC" ]]; then
  echo "No se encuentra $SRC"
  exit 1
fi

echo "→ Copiando configuración Apache…"
cp "$SRC" "$DEST"

echo "→ Habilitando sitio (prioridad sobre la página default)…"
a2ensite "$SITE" >/dev/null

echo "→ Verificando configuración…"
apache2ctl configtest

echo "→ Recargando Apache…"
systemctl reload apache2

echo ""
echo "✓ Listo"
echo "  http://libroclases.ddns.net  →  https://libroclases.ddns.net"
echo ""
echo "Prueba:"
echo "  curl -sI http://libroclases.ddns.net | grep -E 'HTTP|Location'"
