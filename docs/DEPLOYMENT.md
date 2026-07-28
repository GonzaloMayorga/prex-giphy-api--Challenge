# Deployment

Este documento describe la instalación y puesta en funcionamiento de Prex Giphy API mediante Docker Compose.

## Requisitos

- Docker Engine.
- Docker Compose.
- Git.
- Una API key válida de GIPHY.
- Acceso a un repositorio con el código de la aplicación.

No es necesario instalar PHP, Composer, Nginx ni MySQL directamente en el servidor anfitrión.

## 1. Clonar el repositorio

```bash
git clone https://github.com/GonzaloMayorga/prex-giphy-api--Challenge
cd prex-giphy-api--Challenge
```

## 2. Crear el archivo de entorno

```bash
cp .env.example .env
```

Obtener el UID y GID del usuario anfitrión:

```bash
id -u
id -g
```

Configurar como mínimo:

```dotenv
APP_NAME="Prex Giphy API"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://api.example.com
APP_TIMEZONE=UTC

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=prex_giphy
DB_USERNAME=prex
DB_PASSWORD=<secure-database-password>

GIPHY_BASE_URL=https://api.giphy.com/v1
GIPHY_API_KEY=<giphy-api-key>
GIPHY_TIMEOUT=5
GIPHY_CONNECT_TIMEOUT=2

PASSPORT_ACCESS_TOKEN_TTL_MINUTES=30

DEMO_USER_NAME="Challenge User"
DEMO_USER_EMAIL=challenge@example.com
DEMO_USER_PASSWORD=<secure-demo-password>

AUDIT_ENABLED=true
AUDIT_MAX_PAYLOAD_BYTES=1048576

LOCAL_UID=1000
LOCAL_GID=1000
```

Los valores `LOCAL_UID` y `LOCAL_GID` deben coincidir con el usuario que administrará los archivos montados.

## 3. Revisar la configuración de Docker Compose

```bash
docker compose config
```

Este comando valida el archivo `compose.yaml` sin levantar contenedores.

## 4. Construir las imágenes

```bash
docker compose build
```

## 5. Levantar los servicios

```bash
docker compose up -d
```

Verificar:

```bash
docker compose ps
```

Los servicios esperados son:

```text
app
nginx
db
```

La base de datos debe terminar en estado saludable.

## 6. Instalar dependencias PHP

Para una instalación destinada a producción:

```bash
docker compose exec app composer install \
  --no-dev \
  --classmap-authoritative \
  --no-interaction \
  --no-progress
```

Para un entorno de evaluación o desarrollo donde se ejecutarán tests y herramientas de calidad:

```bash
docker compose exec app composer install \
  --no-interaction
```

El archivo `composer.lock` debe estar incluido en el repositorio para instalar versiones reproducibles.

## 7. Generar la clave de Laravel

Solo en una instalación nueva:

```bash
docker compose exec app php artisan key:generate
```

No regenerar `APP_KEY` en una instalación que ya contenga cookies, sesiones o datos cifrados.

Verificar:

```bash
grep '^APP_KEY=' .env
```

## 8. Ejecutar migraciones

```bash
docker compose exec app php artisan migrate --force
```

Verificar:

```bash
docker compose exec app php artisan migrate:status
```

Las migraciones crean:

- Tablas base de Laravel.
- Tablas OAuth de Passport.
- `favorite_gifs`.
- `api_interaction_logs`.

## 9. Configurar Laravel Passport

### Generar claves OAuth

```bash
docker compose exec app php artisan passport:keys --force
```

Este comando genera:

```text
storage/oauth-private.key
storage/oauth-public.key
```

Estas claves:

- No deben almacenarse en Git.
- Deben conservarse entre despliegues del mismo entorno.
- Deben tener permisos de lectura para el proceso PHP.
- La clave privada debe tratarse como un secreto.

### Crear el personal access client

Ejecutar una sola vez por base de datos:

```bash
docker compose exec app php artisan passport:client \
  --personal \
  --provider=users \
  --name="Prex Giphy API Personal Access Client"
```

No recrear innecesariamente el cliente en cada despliegue.

Comprobar los clientes:

```bash
docker compose exec db \
  mysql \
  -u"$DB_USERNAME" \
  -p"$DB_PASSWORD" \
  "$DB_DATABASE" \
  -e "SELECT id, name, provider, revoked FROM oauth_clients;"
```

La ejecución directa anterior requiere que esas variables también estén disponibles en la terminal anfitriona. Alternativamente, puede accederse manualmente al cliente MySQL del contenedor.

## 10. Crear el usuario de demostración

Este paso es opcional en producción, pero puede ser necesario para evaluar el challenge.

Comprobar que estén configuradas:

```dotenv
DEMO_USER_NAME="Challenge User"
DEMO_USER_EMAIL=challenge@example.com
DEMO_USER_PASSWORD=<secure-demo-password>
```

Ejecutar:

```bash
docker compose exec app php artisan db:seed --force
```

El seeder utiliza `updateOrCreate`, por lo que puede ejecutarse nuevamente sin duplicar al usuario por email.

## 11. Preparar permisos

Los directorios de Laravel que requieren escritura son:

```text
storage/
bootstrap/cache/
```

En caso de problemas:

```bash
docker compose exec app chmod -R ug+rwX \
  storage \
  bootstrap/cache
```

No utilizar permisos globales `777`.

## 12. Optimizar Laravel

Después de configurar `.env`, migraciones, Passport y dependencias:

```bash
docker compose exec app php artisan optimize
```

Este comando prepara las cachés compatibles con el despliegue.

También pueden ejecutarse individualmente:

```bash
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan event:cache
docker compose exec app php artisan view:cache
```

Cuando se modifiquen variables o archivos de configuración:

```bash
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan optimize
```

## 13. Verificación del despliegue

### Estado de contenedores

```bash
docker compose ps
```

### Salud HTTP

```bash
curl --fail --silent --show-error \
  http://localhost:8080/up
```

### Versión de PHP

```bash
docker compose exec app php -v
```

### Versión de Laravel

```bash
docker compose exec app php artisan --version
```

### Estado de migraciones

```bash
docker compose exec app php artisan migrate:status
```

### Rutas API

```bash
docker compose exec app php artisan route:list \
  --path=api \
  -vv
```

Se esperan:

```text
POST      api/login
GET|HEAD  api/gifs
GET|HEAD  api/gifs/{id}
POST      api/favorites
```

### Auditoría de dependencias

En un entorno con dependencias de desarrollo instaladas:

```bash
docker compose exec app composer audit --locked
```

### Tests y calidad

En un entorno de evaluación:

```bash
docker compose exec app composer quality
```

## 14. Prueba funcional

### Login

```bash
curl --silent --show-error \
  --request POST \
  --header "Accept: application/json" \
  --header "Content-Type: application/json" \
  --data '{
    "email": "challenge@example.com",
    "password": "<demo-password>"
  }' \
  "http://localhost:8080/api/login"
```

La respuesta debe contener:

```text
data.access_token
data.token_type
data.expires_in
data.expires_at
data.user
```

### Endpoint protegido

Enviar el token:

```bash
curl --silent --show-error \
  --header "Accept: application/json" \
  --header "Authorization: Bearer <access-token>" \
  "http://localhost:8080/api/gifs?query=metalcore&limit=2&offset=0"
```

## 15. Actualizaciones posteriores

Para desplegar una actualización:

```bash
git pull --ff-only
docker compose build
docker compose up -d
```

Instalar las versiones registradas en `composer.lock`:

```bash
docker compose exec app composer install \
  --no-dev \
  --classmap-authoritative \
  --no-interaction \
  --no-progress
```

Ejecutar migraciones pendientes:

```bash
docker compose exec app php artisan migrate --force
```

Regenerar cachés:

```bash
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan optimize
```

No volver a ejecutar `passport:keys --force` salvo que exista una estrategia explícita de rotación de claves.

## 16. Rollback

El código puede volver a un tag o commit anterior:

```bash
git checkout <previous-tag-or-commit>
docker compose build
docker compose up -d
docker compose exec app composer install \
  --no-dev \
  --classmap-authoritative \
  --no-interaction
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan optimize
```

Las migraciones destructivas no deben revertirse automáticamente sin revisar previamente su impacto.

## 17. Logs

Logs de todos los servicios:

```bash
docker compose logs -f
```

Logs de Laravel:

```bash
docker compose exec app tail -f \
  storage/logs/laravel.log
```

Logs de Nginx:

```bash
docker compose logs -f nginx
```

Logs de MySQL:

```bash
docker compose logs -f db
```

Los fallos de auditoría se registran en `laravel.log` sin reemplazar la respuesta principal de la API.

## 18. Copias de seguridad

Antes de una actualización importante:

- Respaldar la base MySQL.
- Respaldar `.env`.
- Respaldar `storage/oauth-private.key`.
- Respaldar `storage/oauth-public.key`.
- Conservar el commit o tag desplegado.

Ejemplo de respaldo de la base:

```bash
docker compose exec -T db \
  mysqldump \
  -uroot \
  -p"<root-password>" \
  prex_giphy \
  > prex_giphy_backup.sql
```

## 19. Seguridad

- Utilizar HTTPS en producción.
- Mantener `APP_DEBUG=false`.
- No versionar `.env`.
- No versionar claves de Passport.
- No versionar la API key de GIPHY.
- No publicar el puerto de MySQL hacia Internet.
- Utilizar contraseñas únicas y robustas.
- Mantener `composer.lock` actualizado y ejecutar `composer audit`.
- Rotar secretos de forma controlada.
- Limitar quién puede leer las claves OAuth.
- Mantener habilitada la auditoría.
- Verificar que contraseñas y tokens se registren como `[REDACTED]`.
- Configurar proxies de confianza cuando la aplicación esté detrás de un balanceador o reverse proxy.
- Conservar fechas en UTC.
- No utilizar permisos `777`.

## 20. Archivos que no deben versionarse

```text
.env
storage/oauth-private.key
storage/oauth-public.key
vendor/
storage/logs/*
```

Comprobar:

```bash
git check-ignore .env
git check-ignore storage/oauth-private.key
git check-ignore storage/oauth-public.key
```
