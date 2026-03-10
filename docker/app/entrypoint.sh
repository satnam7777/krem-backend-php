#!/usr/bin/env bash
set -e

cd /var/www/html

if [ ! -f .env ]; then
  if [ -f .env.docker.example ]; then
    cp .env.docker.example .env
  fi
fi

# Install deps (safe for first run)
if [ -f composer.json ]; then
  composer install --no-interaction --prefer-dist
fi

# Ensure storage perms (in dev bind-mount this may be limited)
mkdir -p storage bootstrap/cache || true

php artisan key:generate --force || true

exec "$@"
