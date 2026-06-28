FROM node:20-alpine AS frontend_builder

WORKDIR /frontend

COPY frontend/package.json frontend/package-lock.json ./
RUN npm ci

COPY frontend/ ./

ARG VUE_APP_API_BASE_URL=/api
ENV VUE_APP_API_BASE_URL=${VUE_APP_API_BASE_URL}

RUN npm run build

FROM nginx:1.27-alpine

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=frontend_builder /frontend/dist/ /usr/share/nginx/html/
COPY backend/public/ /var/www/backend/public/

RUN mkdir -p /var/www/backend/storage/app/public