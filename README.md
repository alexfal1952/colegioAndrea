# Andrea - Laravel API + Nuxt 3

Stack dockerizado con versiones equivalentes a **XAMPP 8.2.12**:

| Componente | Versión |
|------------|---------|
| PHP | 8.2.12 |
| Apache | 2.4.58 |
| MariaDB | 10.4.32 |
| phpMyAdmin | 5.2.1 |
| Laravel | 12.x (compatible con PHP 8.2) |
| Nuxt | 3.x + TypeScript + Pinia |

## Servicios

| Servicio | URL |
|----------|-----|
| API Laravel | http://localhost:8000 |
| Frontend Nuxt 3 | http://localhost:5173 |
| phpMyAdmin | http://localhost:8080 |

## Inicio rápido

```bash
docker compose up --build
```

La primera vez instalará dependencias, ejecutará migraciones y creará un usuario de prueba.

### Usuario de prueba

- **Email:** `admin@andrea.com`
- **Contraseña:** `password123`

## API REST (autenticación con Sanctum)

| Método | Ruta | Descripción |
|--------|------|-------------|
| POST | `/api/register` | Registro |
| POST | `/api/login` | Login (devuelve token Bearer) |
| GET | `/api/user` | Usuario autenticado |
| POST | `/api/logout` | Cerrar sesión |
| POST | `/api/forgot-password` | Solicitar enlace de restablecimiento |
| POST | `/api/reset-password` | Restablecer contraseña con token |

Ejemplo de login:

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"admin@andrea.com","password":"password123"}'
```

## Despliegue en XAMPP

Para producción en XAMPP (sin Docker):

1. Copia `backend/` a `htdocs/andrea-api`
2. Configura Apache con `DocumentRoot` apuntando a `public/`
3. Crea la base de datos MariaDB `andrea`
4. Copia `.env.example` a `.env` y ajusta credenciales de MariaDB
5. Ejecuta `composer install`, `php artisan key:generate`, `php artisan migrate --seed`
6. Compila el frontend con `npm run build` y sirve los archivos estáticos o usa `npm run dev` en desarrollo

## Estructura

```
andrea/
├── backend/     # Laravel REST API + Sanctum
├── frontend/    # Nuxt 3 + TypeScript + Pinia
└── docker-compose.yml
```
