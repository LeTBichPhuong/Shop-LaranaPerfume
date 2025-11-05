# Stage 1: build vendor
FROM composer:2 AS vendor
WORKDIR /app
COPY . .
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# Stage 2: runtime
FROM php:8.2-cli

ENV DEBIAN_FRONTEND=noninteractive
WORKDIR /app

# Cài đặt các gói hệ thống và extension PHP cần thiết
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
    git zip unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
    libpq-dev libicu-dev \
 && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip intl xml \
 && rm -rf /var/lib/apt/lists/*

# Copy toàn bộ mã nguồn và thư viện từ stage composer
COPY --from=vendor /app /app

# Thiết lập biến môi trường cho Render
ENV PORT=10000
EXPOSE 10000

# Tạo và cấp quyền ghi cho cache Laravel
RUN mkdir -p storage/framework/{cache,sessions,views} bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

# Dọn cache cũ và khởi động server
CMD ["bash", "-c", "php artisan config:clear && php artisan migrate --force && php artisan config:cache && php artisan route:cache && php -S 0.0.0.0:${PORT} -t public"]
