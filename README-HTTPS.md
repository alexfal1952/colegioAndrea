# HTTPS con dominio propio (libroclases.ddns.net)

Acceso público con **Let's Encrypt** vía **Caddy compartido** (`~/Documentos/reverse-proxy/`).

| Servicio | URL |
|----------|-----|
| Frontend | https://libroclases.ddns.net |
| API | https://libroclases.ddns.net:8000/api |
| Login local | http://localhost:5173/login |

Ver también el README del proxy compartido para **juegoalex.ddns.net**.

## Requisitos en el router

Reenvío de puertos hacia esta máquina:

| Puerto externo | Servicio |
|----------------|----------|
| **443** | Frontend (HTTPS) |
| **8000** | API backend (HTTPS) |
| **80** | Redirección HTTP → HTTPS (Apache en el host) |

```bash
sudo ~/Documentos/reverse-proxy/scripts/enable-http-redirects.sh
```

## Configuración

Variables en `.env` (raíz del proyecto):

```env
PUBLIC_DOMAIN=libroclases.ddns.net
ACME_EMAIL=admin@andrea.com
NUXT_PUBLIC_API_BASE=https://libroclases.ddns.net:8000/api
FRONTEND_URL=https://libroclases.ddns.net
APP_URL=https://libroclases.ddns.net:8000
CORS_ALLOWED_ORIGINS=https://libroclases.ddns.net,http://localhost:5173
```

## Levantar

```bash
# Solo este proyecto:
docker compose up -d --build

# Luego el proxy compartido (o usa start-all.sh):
cd ~/Documentos/reverse-proxy && docker compose up -d
```

## Verificar

```bash
docker logs shared-caddy --tail 30
curl -sI https://libroclases.ddns.net/login
curl -sI https://libroclases.ddns.net:8000/up
```

## Login demo

- **Email:** `admin@andrea.com`
- **Contraseña:** `123456789`

## Desarrollo local

Sigue funcionando en http://localhost:5173.
