FROM php:8.1-fpm

# Instalar extensões do PHP e dependências do Laravel
RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Definir o diretório de trabalho
WORKDIR /var/www

# Copiar arquivos do Laravel
COPY . .

# Instalar dependências do Laravel
RUN composer install --no-interaction --optimize-autoloader

# Definir permissões para a pasta de cache e logs
RUN chmod -R 777 storage bootstrap/cache

# Roda as migrations do projeto
RUN php artisan migrate --force

CMD ["php-fpm"]
