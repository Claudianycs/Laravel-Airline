#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
php scripts/configure-env.php
composer install --no-interaction --prefer-dist
npm ci --no-audit --no-fund
npm run build
docker compose up -d --wait --wait-timeout 180 mysql phpmyadmin
php artisan migrate --seed --force
echo "Aplicação preparada. Inicie com php artisan serve ou composer dev."
