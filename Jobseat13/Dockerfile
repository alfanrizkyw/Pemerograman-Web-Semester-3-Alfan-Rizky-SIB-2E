FROM php:8.3-cli-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev libonig-dev \
    && docker-php-ext-install pdo_pgsql mbstring \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . .

RUN rm -f includes/config.local.php \
    && chmod -R a=rX /var/www/html

EXPOSE 3000

CMD ["sh", "-c", "exec php -S 0.0.0.0:${PORT:-3000} -t /var/www/html"]
