# ==============================
# Stage 1: Build vendor packages
# ==============================
FROM composer:2 AS vendor
WORKDIR /app
COPY . .
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader


# ==============================
# Stage 2: Runtime environment
# ==============================
FROM php:8.2-cli

ENV DEBIAN_FRONTEND=noninteractive
WORKDIR /app

# Cài đặt các extension cần thiết cho Laravel + PostgreSQL
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
    git zip unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
    libpq-dev libicu-dev \
 && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip intl xml \
 && rm -rf /var/lib/apt/lists/*

# Copy mã nguồn Laravel và vendor từ stage 1
COPY --from=vendor /app /app

# Thiết lập biến môi trường Render
ENV PORT=10000
EXPOSE 10000

# ==============================
# Fix quyền ghi cho Laravel storage & cache
# ==============================
RUN mkdir -p storage/framework/{cache,sessions,views} bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

# ==============================
# Wait for PostgreSQL to be ready
# ==============================
COPY <<'EOF' /wait-for-db.sh
#!/bin/bash
echo "⏳ Waiting for PostgreSQL to be ready..."
until php -r "
try {
    \$dsn = 'pgsql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE');
    \$pdo = new PDO(\$dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
    echo '✅ Database ready!';
    exit(0);
} catch (Exception \$e) {
    echo '.';
    sleep(3);
}
"; do :; done
EOF

RUN chmod +x /wait-for-db.sh

# ==============================
# Start Laravel app
# ==============================
CMD ["bash", "-c", "/wait-for-db.sh && php artisan config:clear && php artisan migrate --force && php artisan config:cache && php artisan route:cache && php -S 0.0.0.0:${PORT} -t public"]
