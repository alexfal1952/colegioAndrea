# Exponer Andrea a internet (Cloudflare Tunnel)

Guía para compartir el proyecto fuera de tu red local sin configurar el router ni abrir puertos manualmente.

Usa **Cloudflare Tunnel** (`cloudflared`): HTTPS gratis y enlace público temporal.

## URL actual del túnel

> Las URLs cambian cada vez que reinicias los túneles. La referencia más reciente está en `scripts/.expose-urls`.

| Servicio | URL (última sesión) |
|----------|---------------------|
| **Frontend (compartir esta)** | https://event-behavioral-schools-cold.trycloudflare.com |
| **API backend** | https://text-virtually-san-applied.trycloudflare.com/api |

### Login demo

- **Email:** `admin@andrea.com`
- **Contraseña:** `123456789`

---

## Comandos

Desde la raíz del proyecto:

```bash
# Crear túneles y mostrar URLs nuevas
./scripts/expose-internet.sh

# Ver URLs guardadas
./scripts/expose-internet.sh status

# Detener túneles
./scripts/expose-internet.sh stop
```

El script:

1. Descarga `cloudflared` en `scripts/.bin/` (si no existe).
2. Crea un túnel para el frontend (puerto **5173**) y otro para la API (puerto **8000**).
3. Reconfigura el frontend para que la API apunte a la URL pública del backend.
4. Guarda las URLs en `scripts/.expose-urls`.

**Requisito:** Docker Compose debe estar levantado (`docker compose up -d`).

---

## Acceso en red local (Wi‑Fi / LAN)

Sin túnel, desde otro dispositivo en la misma red:

| Servicio | URL |
|----------|-----|
| Frontend | `http://<TU_IP_LOCAL>:5173` |
| API | `http://<TU_IP_LOCAL>:8000/api` |

Ejemplo: `http://192.168.1.83:5173`

Los puertos ya escuchan en todas las interfaces (`0.0.0.0`). Si no carga:

```bash
sudo ufw allow 5173/tcp
sudo ufw allow 8000/tcp
```

---

## Variables de entorno

En `docker-compose.yml`:

```yaml
NUXT_PUBLIC_API_BASE: ${NUXT_PUBLIC_API_BASE:-http://localhost:8000/api}
```

Al exponer por túnel, el script define `NUXT_PUBLIC_API_BASE` con la URL pública del backend.

CORS en Laravel acepta dominios `*.trycloudflare.com` (ver `backend/config/cors.php`).

---

## Importante (seguridad)

- Es un entorno de **desarrollo/demo**, no usar con datos reales sensibles.
- **No exponer** MariaDB (`3306`) ni phpMyAdmin (`8080`) a internet.
- El enlace deja de funcionar si apagas el PC o detienes los túneles.
- Cada ejecución de `./scripts/expose-internet.sh` genera **URLs distintas**.

Para producción permanente conviene un VPS o plataforma cloud (Railway, Render, etc.).

---

## Archivos relacionados

| Archivo | Descripción |
|---------|-------------|
| `scripts/expose-internet.sh` | Script principal |
| `scripts/.expose-urls` | URLs de la última sesión |
| `scripts/.logs/tunnel-*.log` | Logs de cloudflared |
| `scripts/.pids/tunnel-*.pid` | PIDs para detener túneles |
