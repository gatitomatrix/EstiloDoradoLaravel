# Estilo Dorado en Render (web + admin)

Auto-Deploy debe quedar en **No**. Local (XAMPP) no se toca.

Nombres sugeridos (cámbialos si Render dice que están ocupados):

- API: `estilo-dorado-api` → `https://estilo-dorado-api.onrender.com`
- Web: `estilo-dorado-web` → `https://estilo-dorado-web.onrender.com`
- MySQL: `estilo-dorado-db`

---

## 0. En tu PC (una sola vez)

### Clave de Laravel

En la carpeta de Laravel:

```powershell
php artisan key:generate --show
```

Copia la línea `base64:...` (la vas a pegar en Render).

### Dump de la base

Copia tu SQL de phpMyAdmin a:

`EstiloDoradoLaravel/database/sql/estilo_dorado.sql`

```powershell
cd E:\ESTILO_DORADO\PROYECTO_FINAL_ESTILO_DORADO\EstiloDoradoLaravel
# ajusta la ruta del dump:
copy E:\ESTILO_DORADO\...\estilo_dorado_bd.sql database\sql\estilo_dorado.sql
git add database/sql/estilo_dorado.sql
git commit -m "chore: dump MySQL para importar en Render"
git push origin main
```

Luego: `git pull` de Laravel **y** Angular (los cambios de este paso).

---

## 1. MySQL en Render

1. Dashboard → **New** → **Private Service** (o plantilla **MySQL** si te aparece).
2. Si te pide repo: usa la plantilla oficial MySQL de Render, o imagen Docker `mysql:8.0`.
3. Disco persistente: **sí** (mínimo 1 GB).
4. Variables:

| Key | Value |
|-----|--------|
| `MYSQL_DATABASE` | `estilo_dorado` |
| `MYSQL_USER` | `edorado` |
| `MYSQL_PASSWORD` | (inventa una, guárdala) |
| `MYSQL_ROOT_PASSWORD` | (otra, guárdala) |

5. Anota el **hostname interno** (algo como `estilo-dorado-db:3306`).

Si Render te ofrece **MySQL managed** con host y puerto, usa esos datos. Da igual el nombre del servicio.

---

## 2. API Laravel (Web Service)

1. **New → Web Service**.
2. Repo: `EstiloDoradoLaravel`, rama `main`.
3. Runtime: **Docker**.
4. Instance: **Free**.
5. **Auto-Deploy: No**.
6. Health check: `/up`.
7. Environment:

| Key | Value |
|-----|--------|
| `APP_NAME` | `Estilo Dorado` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | lo de `php artisan key:generate --show` |
| `APP_URL` | `https://estilo-dorado-api.onrender.com` (la URL que te dé Render) |
| `FRONTEND_URLS` | `*` |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | hostname interno del MySQL |
| `DB_PORT` | `3306` |
| `DB_DATABASE` | `estilo_dorado` |
| `DB_USERNAME` | `edorado` |
| `DB_PASSWORD` | la que inventaste |
| `SESSION_DRIVER` | `file` |
| `CACHE_STORE` | `file` |
| `QUEUE_CONNECTION` | `sync` |
| `LLM_DRIVER` | `rules` |
| `LLM_FALLBACK_RULES` | `true` |
| `IMPORT_SQL` | `1` **solo el primer deploy** |

8. **Create Web Service** y espera a que ponga **Live**.
9. Prueba en el navegador: `https://TU-API.onrender.com/up` → debe decir OK / `{"status":"ok"}`.
10. Cuando la tienda ya liste productos: pon `IMPORT_SQL` = `0` (o bórrala) y **no** redespliegues aún.

Si el import no corrió (el contenedor no tenía el `.sql`):

```
# Shell del Web Service en Render
sh /var/www/scripts/render-start.sh
```

mejor: vuelve a deploy **una vez** con el dump ya en `main` e `IMPORT_SQL=1`.

---

## 3. Web Angular (Static Site)

1. **New → Static Site**.
2. Repo: `EstiloDoradoAngular`, rama `main`.
3. **Auto-Deploy: No**.
4. Build command:

```
npm ci && npm run build
```

5. Publish directory:

```
dist/estilo-dorado/browser
```

6. Environment:

| Key | Value |
|-----|--------|
| `API_BASE_URL` | `https://TU-API.onrender.com/api` |

(con `/api` al final, sin barra extra)

7. **Create Static Site**.
8. Cuando esté Live, abre la URL. Debes ver la tienda.
9. Admin: `https://TU-WEB.onrender.com/admin/login`.

---

## 4. Congelar

En **los dos** servicios (API y Static):

Settings → **Auto-Deploy** → **No**.

GitHub puede seguir recibiendo commits. Render no se mueve hasta **Manual Deploy**.

---

## Si algo falla

| Síntoma | Qué mirar |
|---------|-----------|
| API en rojo | Logs del Web Service (Docker / `APP_KEY` / MySQL host) |
| `/up` OK pero la web vacía | `API_BASE_URL` mal (falta `/api`) |
| CORS en consola | `FRONTEND_URLS=*` en la API y Manual Deploy de la API |
| Sin productos | dump no importó; `IMPORT_SQL=1` + dump en el repo + un Manual Deploy |
| Primera carga lenta | plan Free: el servicio se duerme; espera 1 minuto |

Local no cambia: `php artisan serve`, `ng serve`, emulador como siempre.
