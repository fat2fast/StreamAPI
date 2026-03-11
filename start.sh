#!/usr/bin/env bash
set -euo pipefail

docker compose up -d --build

echo "Services are up."
echo "Run composer install: docker compose exec api composer install"
echo "Run migrations manually: docker compose exec api php yii migrate --interactive=0"
