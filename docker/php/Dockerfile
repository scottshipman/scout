FROM php:8.4-apache

LABEL project="Scout"

# --- System dependencies ---
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        zip \
        curl \
        libicu-dev \
        libzip-dev \
        libonig-dev \
        default-mysql-client \
    && docker-php-ext-install intl pdo pdo_mysql mysqli zip opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && a2enmod rewrite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# --- PHP configuration ---
COPY php.ini /usr/local/etc/php/conf.d/99-scout.ini

# --- Apache virtual host (Symfony public/ as document root) ---
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# --- Composer ---
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# --- Symfony CLI (used to bootstrap the project) ---
# Copied directly from the official image rather than piping the shell
# installer script, which changed its install path (~/.symfony5 ->
# ~/.config/symfony-cli) and is fragile to script against in a Dockerfile.
COPY --from=ghcr.io/symfony-cli/symfony-cli:latest /usr/local/bin/symfony /usr/local/bin/symfony

WORKDIR /var/www/html

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
