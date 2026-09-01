#!/usr/bin/env bash
# Expone Andrea (frontend + API) a internet con Cloudflare Tunnel (gratis, sin abrir puertos en el router).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
BIN_DIR="$ROOT/scripts/.bin"
CLOUDFLARED="$BIN_DIR/cloudflared"
PID_DIR="$ROOT/scripts/.pids"
LOG_DIR="$ROOT/scripts/.logs"
URL_FILE="$ROOT/scripts/.expose-urls"

FRONTEND_PORT="${FRONTEND_PORT:-5173}"
BACKEND_PORT="${BACKEND_PORT:-8000}"

mkdir -p "$BIN_DIR" "$PID_DIR" "$LOG_DIR"

red() { printf '\033[0;31m%s\033[0m\n' "$*"; }
green() { printf '\033[0;32m%s\033[0m\n' "$*"; }
yellow() { printf '\033[1;33m%s\033[0m\n' "$*"; }

stop_tunnels() {
  for name in frontend backend; do
    local pid_file="$PID_DIR/tunnel-$name.pid"
    if [[ -f "$pid_file" ]]; then
      local pid
      pid="$(cat "$pid_file")"
      if kill -0 "$pid" 2>/dev/null; then
        kill "$pid" 2>/dev/null || true
        yellow "Túnel $name detenido (pid $pid)"
      fi
      rm -f "$pid_file"
    fi
  done
}

install_cloudflared() {
  if [[ -x "$CLOUDFLARED" ]]; then
    return
  fi

  yellow "Descargando cloudflared…"
  ARCH="$(uname -m)"
  case "$ARCH" in
    x86_64) CF_ARCH="amd64" ;;
    aarch64|arm64) CF_ARCH="arm64" ;;
    *) red "Arquitectura no soportada: $ARCH"; exit 1 ;;
  esac

  TMP="$BIN_DIR/cloudflared.tgz"
  curl -fsSL "https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-${CF_ARCH}" -o "$CLOUDFLARED"
  chmod +x "$CLOUDFLARED"
  green "cloudflared instalado en $CLOUDFLARED"
}

wait_for_tunnel_url() {
  local log_file="$1"
  local timeout="${2:-90}"
  local url=""
  local i=0

  while [[ $i -lt $timeout ]]; do
    url="$(grep -oE 'https://[a-z0-9-]+\.trycloudflare\.com' "$log_file" | head -1 || true)"
    if [[ -n "$url" ]]; then
      echo "$url"
      return 0
    fi
    sleep 1
    i=$((i + 1))
  done

  red "No se obtuvo URL del túnel. Revisa: $log_file"
  return 1
}

start_tunnel() {
  local name="$1"
  local port="$2"
  local log_file="$LOG_DIR/tunnel-$name.log"
  local pid_file="$PID_DIR/tunnel-$name.pid"

  : > "$log_file"
  nohup "$CLOUDFLARED" tunnel --no-autoupdate --url "http://127.0.0.1:$port" >>"$log_file" 2>&1 &
  local pid=$!
  disown "$pid" 2>/dev/null || true
  echo "$pid" > "$pid_file"
  yellow "Iniciando túnel $name (puerto $port)…" >&2
  wait_for_tunnel_url "$log_file"
}

ensure_docker() {
  if ! docker compose -f "$ROOT/docker-compose.yml" ps --status running 2>/dev/null | grep -q andrea-frontend; then
    yellow "Levantando Docker Compose…"
    docker compose -f "$ROOT/docker-compose.yml" up -d
    sleep 5
  fi
}

configure_frontend_api() {
  local backend_public_url="$1"
  export NUXT_PUBLIC_API_BASE="${backend_public_url%/}/api"

  yellow "Configurando frontend para usar API pública…"
  docker compose -f "$ROOT/docker-compose.yml" up -d --force-recreate frontend >/dev/null

  sleep 8
}

print_lan_info() {
  local lan_ip
  lan_ip="$(hostname -I 2>/dev/null | awk '{print $1}' || true)"
  if [[ -n "$lan_ip" ]]; then
    echo ""
    yellow "Acceso en tu red local (Wi‑Fi/LAN):"
    echo "  Frontend: http://${lan_ip}:${FRONTEND_PORT}"
    echo "  API:      http://${lan_ip}:${BACKEND_PORT}/api"
    echo ""
    echo "  Si no carga, permite el firewall:"
    echo "  sudo ufw allow ${FRONTEND_PORT}/tcp && sudo ufw allow ${BACKEND_PORT}/tcp"
  fi
}

main() {
  cd "$ROOT"

  if [[ "${1:-}" == "stop" ]]; then
    stop_tunnels
    green "Túneles detenidos."
    exit 0
  fi

  if [[ "${1:-}" == "status" ]]; then
    if [[ -f "$URL_FILE" ]]; then
      cat "$URL_FILE"
    else
      yellow "No hay túneles activos. Ejecuta: ./scripts/expose-internet.sh"
    fi
    exit 0
  fi

  stop_tunnels
  install_cloudflared
  ensure_docker

  BACKEND_URL="$(start_tunnel backend "$BACKEND_PORT")"
  FRONTEND_URL="$(start_tunnel frontend "$FRONTEND_PORT")"

  configure_frontend_api "$BACKEND_URL"

  cat > "$URL_FILE" <<EOF
FRONTEND_URL=$FRONTEND_URL
BACKEND_URL=$BACKEND_URL
API_BASE=${BACKEND_URL}/api
CREATED_AT=$(date -Iseconds)
EOF

  echo ""
  green "══════════════════════════════════════════════"
  green "  Andrea visible desde internet"
  green "══════════════════════════════════════════════"
  echo ""
  echo "  Comparte este enlace (frontend):"
  echo "  👉 $FRONTEND_URL"
  echo ""
  echo "  Login demo: admin@andrea.com / 123456789"
  echo ""
  echo "  API pública: ${BACKEND_URL}/api"
  echo ""
  yellow "  Mantén esta terminal abierta mientras muestres el demo."
  yellow "  Para detener: ./scripts/expose-internet.sh stop"
  echo ""

  print_lan_info
}

main "$@"
