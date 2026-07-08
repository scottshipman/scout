# Scout

A ready-to-run Docker environment for a Symfony application, backed by MySQL
and Redis.

## What's inside

| Service    | Image / Base          | Purpose                                   |
|------------|------------------------|--------------------------------------------|
| `php`      | `php:8.4-apache` (custom) | Serves the Symfony app on port `8080`   |
| `database` | `mysql:8.4`             | Application database                      |
| `redis`    | `redis:7-alpine`        | Cache / session storage                   |

The Symfony application lives in `./app` and is committed to this repo, so
cloning it gives you the full app — controllers, templates, entities,
migrations, assets — not a bare skeleton. The `php` container's entrypoint
runs `composer install` on every start (fast, no-op once `vendor/` is
current), waits for MySQL, and applies any pending Doctrine migrations
before serving the app.

If `./app` ever has no `composer.json` (e.g. an empty clone or after the
reset procedure below), the entrypoint falls back to bootstrapping a fresh
Symfony project via the Symfony CLI installer — that path needs internet
access to Packagist/GitHub, but it's a fallback, not the normal flow.

## Requirements

- Docker Engine 24+
- Docker Compose v2 (bundled with recent Docker Desktop / `docker compose`)

## Quick start

```bash
git clone git@github.com:scottshipman/scout.git
cd scout

# Copy the example env and adjust credentials/ports if you want
cp .env.example .env

# Build the images and start MySQL, Redis, and PHP
docker compose up -d --build

# Tail the logs while Composer installs and migrations run
docker compose logs -f php
```

The first run typically takes a minute or two while Composer installs
dependencies and migrations apply. Once it's done, open:

```
http://localhost:8080
```

You should see the app Home page.

## Configuration

All tunables live in the root `.env` file (used by `docker-compose.yml` for
variable substitution). `.env` is gitignored since it holds local
credentials/ports — copy `.env.example` to `.env` and adjust as needed:

```
APP_PORT=8080          # host port the app is served on
SYMFONY_VERSION=        # blank = latest stable; or pin e.g. "7.4"
MYSQL_ROOT_PASSWORD=...
MYSQL_DATABASE=scout
MYSQL_USER=scout
MYSQL_PASSWORD=...
MYSQL_PORT=3306
REDIS_PORT=6379
```

The Symfony app itself receives its runtime configuration through
`app/.env.local`, which is generated automatically from these same values
(`DATABASE_URL` and `REDIS_URL`) every time the container starts.

## Common commands

A `Makefile` is included for convenience:

```bash
make up          # docker compose up -d --build
make down        # docker compose down
make sh          # shell into the php container
make console -- about        # php bin/console about
make composer -- require symfony/mailer
make logs
make status
```

(Or just use `docker compose exec php ...` directly if you prefer.)

## Resetting to a bare Symfony skeleton

Delete the contents of `./app` (everything except `.gitkeep`) and restart
the stack — the entrypoint script will detect there's no `composer.json`
and bootstrap a fresh, empty Symfony project instead of using the committed
app:

```bash
rm -rf app/*
docker compose up -d --build
```

## Project layout

```
Scout/
├── docker-compose.yml       # mysql + redis + php services
├── .env.example              # template for the local .env (copy to .env)
├── Makefile                 # convenience commands
├── docker/
│   └── php/
│       ├── Dockerfile          # php:8.4-apache + extensions + composer + symfony-cli
│       ├── entrypoint.sh       # installs deps, runs migrations, falls back to bootstrapping if app/ is empty
│       ├── php.ini             # PHP overrides (opcache, limits, timezone)
│       └── 000-default.conf    # Apache vhost pointing at Symfony's public/
└── app/                     # Symfony application (committed — controllers, templates, entities, etc.)
```
