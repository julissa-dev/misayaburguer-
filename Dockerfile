# Etapa 1: Node para compilar los assets
FROM node:18 AS node_modules
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Etapa 2: PHP + Apache para Laravel
FROM php:8.2-apache

# Instalar extensiones PHP necesarias
RUN apt-get update && apt-get install -y \
    libonig-dev zip unzip libzip-dev git curl libpng-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Habilitar mod_rewrite para Laravel
RUN a2enmod rewrite

# Copiar Composer desde la imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar archivos del proyecto Laravel
COPY . /var/www/html

# Copiar los assets ya compilados de Vite
COPY --from=node_modules /app/public/build /var/www/html/public/build

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Instalar dependencias PHP
RUN composer install --no-dev --optimize-autoloader

# Eliminar la copia del .env.example para que Railway inyecte las variables
# REMOVER ESTA LÍNEA: COPY .env.example .env

# Ejecutar comandos necesarios de Laravel (sin la clave, Railway la inyecta)
# REMOVER ESTA LÍNEA: RUN php artisan key:generate
RUN php artisan storage:link
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# Establecer permisos correctos
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Servir Laravel desde /public
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Exponer el puerto
EXPOSE 80

# === CAMBIO CRÍTICO AQUÍ: MODIFICAR CMD ===
# Este es el comando que se ejecuta cuando el contenedor inicia.
# Incluye la espera por la DB, migraciones y luego inicia Apache.
CMD ["/bin/bash", "-c", "\
    echo 'Waiting for MySQL...' && \
    until nc -z mysql.railway.internal 3306; do \
        echo 'MySQL is unavailable - sleeping' && sleep 5; \
    done && \
    echo 'MySQL is up - executing migrations' && \
    sleep 5 && \
    php artisan migrate --force && \
    apache2-foreground\
"]