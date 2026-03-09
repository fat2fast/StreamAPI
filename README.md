# Yii2 Livestream API

Backend API for streamer/audience applications using PHP 8.2, Yii2, MySQL, and Docker.

## Quick start

1. Start services:

```bash
./start.sh
```

2. Install dependencies inside PHP container:

```bash
docker compose exec php composer install
```

3. Run migrations manually with Yii2:

```bash
docker compose exec php php yii migrate --interactive=0
```

Important:

- `start.sh` does **not** run migrations.
- Migration is a separate required step and must be executed manually via Yii2 command.

## Service endpoints

- API base URL: `http://localhost:8080`
- MySQL: `localhost:3306`
