#!/usr/bin/env bash
set -euo pipefail

docker compose up -d --build

echo "Services are up."
echo "Run migrations manually: docker compose exec php php yii migrate --interactive=0"
