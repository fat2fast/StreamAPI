# Yii2 Livestream API

Backend API for streamer/audience applications using PHP 8.2, Yii2, MySQL, and Docker.

## Quick start

1. Create environment file:

```bash
cp .env.example .env
```

2. Start services:

```bash
./start.sh
```

3. Install dependencies inside PHP container:

```bash
docker compose exec api composer install
```

4. Run migrations manually with Yii2:

```bash
docker compose exec api php yii migrate --interactive=0
```

Important:

- `start.sh` does **not** run migrations.
- Migration is a separate required step and must be executed manually via Yii2 command.
- `docker-compose.yml` loads runtime variables from `.env`.

## Service endpoints

- API base URL: `http://localhost:9003`
- MySQL: `localhost:3306`
