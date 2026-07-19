# Docker del sistema

Esta dockerización deja el sistema listo para:

- desarrollo local con recarga de código en backend y rebuild vigilado del frontend,
- despliegue tipo producción con Nginx sirviendo el frontend y reenviando `/api` al backend Laravel,
- MySQL 8.4 LTS como base de datos del stack.

## Servicios

- `db`: MySQL 8.4 LTS.
- `app`: Laravel sobre PHP 8.3 FPM.
- `web`: Nginx sirviendo el frontend compilado y exponiendo `/api`, `/storage` y `/up` hacia Laravel.
- `frontend-builder`: solo en desarrollo; recompila el frontend en modo watch.

## 1. Preparar variables

Desde la raíz del proyecto:

```bash
cp .env.docker.example .env.docker
```

Ajusta al menos estos valores en `.env.docker`:

- `APP_URL`
- `APP_KEY`
- `MYSQL_ROOT_PASSWORD`
- `MYSQL_PASSWORD`
- `WEB_PORT`

## 2. Generar `APP_KEY`

Primero construye la imagen del backend:

```bash
docker compose --env-file .env.docker build app
```

Luego genera una clave y cópiala en `APP_KEY` dentro de `.env.docker`:

```bash
docker compose --env-file .env.docker run --rm app php artisan key:generate --show
```

## 3. Levantar entorno local

```bash
docker compose --env-file .env.docker -f docker-compose.yml -f docker-compose.dev.yml up --build
```

Accesos por defecto:

- aplicación: `http://localhost:8080`
- MySQL expuesto al host: `localhost:3306`

Notas de desarrollo:

- El backend queda montado desde `./backend`.
- El frontend se compila en watch mediante `frontend-builder` y Nginx sirve el resultado.
- El frontend usa `/api` como base y ya no depende de `localhost:8000` hardcodeado.

## 4. Migraciones y seeders

Con los contenedores arriba:

```bash
docker compose --env-file .env.docker exec app php artisan migrate --force
docker compose --env-file .env.docker exec app php artisan db:seed --force
```

Si no quieres datos semilla:

```bash
docker compose --env-file .env.docker exec app php artisan migrate --force
```

## 5. Levantar modo producción

```bash
docker compose --env-file .env.docker up -d --build
```

En este modo:

- el frontend queda compilado dentro de la imagen de Nginx,
- el backend corre sobre PHP-FPM,
- la base de datos no se publica al host salvo que uses el override de desarrollo.

## 6. Comandos útiles

Ver logs:

```bash
docker compose --env-file .env.docker logs -f web
docker compose --env-file .env.docker logs -f app
docker compose --env-file .env.docker logs -f db
```

Entrar al backend:

```bash
docker compose --env-file .env.docker exec app sh
```

Abrir MySQL:

```bash
docker compose --env-file .env.docker exec db mysql -ucruzroja -p
```

## 7. Observaciones importantes

- Si cambias el dominio o puerto público, actualiza `APP_URL`, `SESSION_DOMAIN` y, si lo usas, `SANCTUM_STATEFUL_DOMAINS`.
- Los archivos subidos quedan persistidos en el volumen `backend_storage`.
- El archivo previo `docker-compose.db.yml` puede seguir usándose si solo quieres la base de datos, pero el stack completo ahora vive en `docker-compose.yml`.