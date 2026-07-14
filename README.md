# Sistema de gestión de Cruz Roja

Aplicación web para administrar voluntarios, actividades, hojas de vida, documentos, boletas, galerías, portada pública y notificaciones por correo.

## Estructura

- `frontend`: aplicación Vue 3.
- `backend`: API Laravel 12 y trabajador de colas.
- `docker`: configuración de Nginx, PHP y las imágenes de despliegue.
- `docs`: documentación de instalación y formatos institucionales de referencia.

## Desarrollo local

```bash
cd backend
php artisan serve
php artisan queue:work
```

```bash
cd frontend
npm ci
npm run serve
```

## Docker

La instalación completa se encuentra en [docs/docker.md](./docs/docker.md). Copia `.env.docker.example` como `.env.docker`, completa sus valores privados y luego inicia los servicios con Docker Compose.
