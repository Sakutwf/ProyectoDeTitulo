# Migración base de datos a Linux con Docker

Estado actual del proyecto:

- El backend Laravel soporta `mysql` y `mariadb`.
- La configuración por defecto del repositorio sigue apuntando a `sqlite`.
- Si quieres una base de datos SQL en el servidor Linux, la ruta más limpia para este proyecto es usar MariaDB en Docker.

## 1. Instalar Docker en Ubuntu Server

Asumiendo Ubuntu 22.04 o 24.04:

```bash
sudo apt update
sudo apt install ca-certificates curl
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc

sudo tee /etc/apt/sources.list.d/docker.sources <<EOF
Types: deb
URIs: https://download.docker.com/linux/ubuntu
Suites: $(. /etc/os-release && echo "${UBUNTU_CODENAME:-$VERSION_CODENAME}")
Components: stable
Architectures: $(dpkg --print-architecture)
Signed-By: /etc/apt/keyrings/docker.asc
EOF

sudo apt update
sudo apt install docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
sudo docker run hello-world
```

Si quieres usar Docker sin `sudo`:

```bash
sudo groupadd docker
sudo usermod -aG docker $USER
newgrp docker
docker run hello-world
```

## 2. Levantar MariaDB con Docker Compose

Desde la raíz del proyecto:

```bash
export MARIADB_ROOT_PASSWORD='cambia_root'
export MARIADB_DATABASE='SistemaCruzRoja'
export MARIADB_USER='cruzroja'
export MARIADB_PASSWORD='cambia_usuario'

docker compose -f docker-compose.db.yml up -d
docker compose -f docker-compose.db.yml ps
```

## 3. Configurar Laravel para usar MariaDB

En el servidor Linux, copia `backend/.env.mariadb.example` como `backend/.env` y ajusta:

```env
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=SistemaCruzRoja
DB_USERNAME=cruzroja
DB_PASSWORD=cambia_usuario
```

## 4. Crear estructura de la base de datos

Desde `backend/`:

```bash
php artisan key:generate
php artisan migrate
php artisan db:seed
```

Si no quieres poblarla con datos de prueba:

```bash
php artisan key:generate
php artisan migrate
```

## 5. Verificar conexión

```bash
php artisan migrate:status
```

Y opcionalmente:

```bash
docker exec -it sistema-cruz-roja-db mariadb -ucruzroja -p
```

Ese último comando te pedirá la contraseña del usuario `cruzroja`. Si cambias el usuario, ajusta también el valor después de `-u`.

## 6. Nota importante sobre datos existentes

Hoy el repositorio está orientado a levantar la estructura por migraciones. Si tienes datos reales en SQLite o en otro motor y quieres conservarlos, el siguiente paso ya no es solo “instalar la base”, sino planificar una migración de datos.

En ese caso conviene:

1. identificar la base actual real,
2. exportar sus datos,
3. mapearlos al esquema nuevo,
4. importarlos en MariaDB.

Si quieres, en el siguiente paso te preparo también el `docker-compose` completo del backend + frontend + MariaDB para el servidor Linux.
