#!/bin/bash
set -e

APP_DIR=/var/www/html

# ---------------------------------------------------------------------------
# 1. Bootstrap a new Symfony project if one doesn't already exist in ./app
# ---------------------------------------------------------------------------
if [ ! -f "$APP_DIR/composer.json" ]; then
    echo "==> No Symfony project found in ./app — bootstrapping a new one..."

    TMP_DIR=$(mktemp -d)

    if [ -n "$SYMFONY_VERSION" ]; then
        echo "==> Installing Symfony ${SYMFONY_VERSION} (pinned via SYMFONY_VERSION)"
        symfony new "$TMP_DIR" --version="$SYMFONY_VERSION" --webapp --no-git
    else
        echo "==> Installing the latest stable Symfony release"
        symfony new "$TMP_DIR" --webapp --no-git
    fi

    shopt -s dotglob
    mv "$TMP_DIR"/* "$APP_DIR"/
    shopt -u dotglob
    rm -rf "$TMP_DIR"

    cd "$APP_DIR"

    echo "==> Adding MySQL (Doctrine) and Redis support..."
    composer require --no-interaction \
        symfony/orm-pack \
        doctrine/doctrine-migrations-bundle \
        snc/redis-bundle

    echo "==> Symfony project bootstrapped successfully."
fi

cd "$APP_DIR"

# ---------------------------------------------------------------------------
# 2. Wire up environment variables coming from docker-compose
# ---------------------------------------------------------------------------
cat > "$APP_DIR/.env.local" <<EOF
APP_ENV=${APP_ENV:-dev}
DATABASE_URL=${DATABASE_URL}
REDIS_URL=${REDIS_URL}
EOF

# ---------------------------------------------------------------------------
# 3. Wait for MySQL to accept connections
#
# We deliberately use PHP's own pdo_mysql (mysqlnd) driver here rather than
# the `mysqladmin` client binary. Debian's `default-mysql-client` is actually
# MariaDB's client, which can fail to negotiate MySQL 8's default
# `caching_sha2_password` auth plugin — that failure was previously hidden
# because stderr was redirected to /dev/null, making it look like MySQL
# never became ready. Using mysqlnd avoids the mismatch entirely (it's the
# same driver Doctrine uses at runtime), and we print the real error if the
# wait drags on.
# ---------------------------------------------------------------------------
echo "==> Waiting for MySQL to be ready..."
ATTEMPTS=0
until php -r '
try {
    new PDO(
        "mysql:host=database;port=3306;dbname=" . getenv("MYSQL_DATABASE"),
        getenv("MYSQL_USER"),
        getenv("MYSQL_PASSWORD")
    );
    exit(0);
} catch (\PDOException $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}
'; do
    ATTEMPTS=$((ATTEMPTS + 1))
    if [ $((ATTEMPTS % 5)) -eq 0 ]; then
        echo "==> Still waiting for MySQL after ${ATTEMPTS} attempts (see error above, if any)..."
    fi
    sleep 2
done
echo "==> MySQL is ready."

# ---------------------------------------------------------------------------
# 4. Make sure dependencies are installed and consistent.
#
# We always run this (not just "if vendor/ is missing") on purpose: if a
# previous start got killed mid-install (e.g. during a crash-restart loop),
# vendor/ can exist but be incomplete — missing packages like
# symfony/runtime while composer.json/composer.lock still reference them.
# Running `composer install` unconditionally self-heals that, and is a
# cheap no-op once everything's already correctly installed.
# ---------------------------------------------------------------------------
echo "==> Installing/verifying composer dependencies..."
composer install --no-interaction --optimize-autoloader

# ---------------------------------------------------------------------------
# 5. Create the database and run migrations, if the console exists
# ---------------------------------------------------------------------------
if [ -f "$APP_DIR/bin/console" ]; then
    php bin/console doctrine:database:create --if-not-exists --no-interaction || true
    if [ -d "$APP_DIR/migrations" ] && [ "$(ls -A "$APP_DIR/migrations" 2>/dev/null)" ]; then
        php bin/console doctrine:migrations:migrate --no-interaction || true
    fi
fi

chown -R www-data:www-data "$APP_DIR/var" 2>/dev/null || true

exec "$@"
