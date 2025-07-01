FROM php:8.2-apache

# Instalar extensiones requeridas
RUN apt-get update && apt-get install -y \
    libonig-dev zip unzip libzip-dev git curl libpng-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Habilitar mod_rewrite para Laravel
RUN a2enmod rewrite

# Copiar archivos del proyecto Laravel al contenedor
COPY . /var/www/html

# Establecer el directorio de trabajo a public/
WORKDIR /var/www/html

# Configurar Apache para que sirva desde public/
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Instalar dependencias PHP
RUN composer install --no-dev --optimize-autoloader

# Dar permisos a las carpetas necesarias
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
