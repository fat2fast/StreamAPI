#!/usr/bin/env bash
set -euo pipefail

docker compose up -d --build

echo "Waiting for MySQL to become ready..."
for i in {1..30}; do
  if docker compose exec -T db sh -lc 'mysqladmin ping -h 127.0.0.1 -uroot -p"$MYSQL_ROOT_PASSWORD" --silent' >/dev/null 2>&1; then
    break
  fi
  if [ "$i" -eq 30 ]; then
    echo "MySQL is not ready after timeout."
    exit 1
  fi
  sleep 2
done

echo "Installing PHP dependencies if needed..."
docker compose exec -T api sh -lc 'if [ ! -f vendor/autoload.php ]; then composer install --no-interaction --prefer-dist; fi'

echo "Running database migrations..."
docker compose exec -T api php yii migrate --interactive=0

echo "Services are up and migrated."
echo "API: http://localhost:9003"
echo "Swagger (dev only): http://localhost:9003/docs"
