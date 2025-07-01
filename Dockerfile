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

COPY .env.example .env
# Ejecutar comandos necesarios de Laravel
RUN php artisan key:generate
RUN php artisan storage:link
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache
RUN php artisan migrate --force

# Establecer permisos correctos
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Servir Laravel desde /public
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Exponer el puerto
EXPOSE 80

# Iniciar Apache en primer plano
CMD ["apache2-foreground"]
