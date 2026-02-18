# Deploy CV UNAMIS en DigitalOcean + Akky

## 0) Supuestos
- Ya tienes un Droplet funcionando con tus otros proyectos.
- Akky ya gestiona apps/domains/SSL en ese droplet.
- El codigo vivira en GitHub bajo la cuenta `unamismx`.

## 1) Crear repositorio en GitHub
Nombre sugerido: `cv-unamis-src`

Comandos locales (cuando ya tengas codigo base):
```bash
cd /Users/jriveramx/Documents/cv-unamis-src
git init
git add .
git commit -m "feat: bootstrap cv unamis"
git branch -M main
git remote add origin git@github.com:unamismx/cv-unamis-src.git
git push -u origin main
```

## 2) DNS para subdominio
En tu proveedor DNS de `unamis.mx`:
- Crear registro `A`
- Host: `cv`
- Valor: `IP_PUBLICA_DEL_DROPLET`
- TTL: 300 (o automatico)

Verificacion:
```bash
dig +short cv.unamis.mx
```
Debe responder la IP de tu droplet.

## 3) Crear app en Akky
Usa la misma plantilla/configuracion que ya usas en los otros proyectos Laravel.

Campos recomendados:
- App name: `cv-unamis`
- Domain: `cv.unamis.mx`
- Source: `GitHub`
- Repo: `unamismx/cv-unamis-src`
- Branch: `main`
- Runtime: `PHP 8.3` (igual que tus apps actuales)
- Web root: `public`

Build/Deploy commands (si Akky los pide):
```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate --force
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Si usas Vite assets:
```bash
npm ci
npm run build
```

## 4) Variables de entorno en Akky
Minimo:
```env
APP_NAME="CV UNAMIS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://cv.unamis.mx
APP_LOCALE=es
APP_FALLBACK_LOCALE=es

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cv_unamis
DB_USERNAME=cv_unamis_user
DB_PASSWORD=TU_PASSWORD

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_DOMAIN=cv.unamis.mx

CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
```

## 5) Base de datos en droplet
Crear DB y usuario (si no lo hace Akky automaticamente):
```sql
CREATE DATABASE cv_unamis CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'cv_unamis_user'@'localhost' IDENTIFIED BY 'TU_PASSWORD';
GRANT ALL PRIVILEGES ON cv_unamis.* TO 'cv_unamis_user'@'localhost';
FLUSH PRIVILEGES;
```

## 6) SSL
En Akky:
- Activar `Let's Encrypt` para `cv.unamis.mx`
- Activar redireccion `HTTP -> HTTPS`

## 7) Post-deploy checklist
- `https://cv.unamis.mx` responde 200.
- Login funciona.
- `php artisan migrate --force` aplicado sin errores.
- Se puede crear CV en ES y EN.
- PDF genera en ambas versiones.

## 8) Recomendacion operativa
- Mantener mismo flujo que viaticos: `main` protegido + deploy automatico al hacer merge.
- Crear entorno `staging` opcional: `staging-cv.unamis.mx` antes de produccion.
