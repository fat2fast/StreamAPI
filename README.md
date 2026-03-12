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

5. Generate test tokens for seeded users:

```bash
docker compose exec api php yii token/generate 1 streamer
docker compose exec api php yii token/generate 2 audience
```

Important:

- `start.sh` does **not** run migrations.
- Migration is a separate required step and must be executed manually via Yii2 command.
- `docker-compose.yml` loads runtime variables from `.env`.
- Initial seeded users are inserted by migration (`streamer@test.local`, `audience@test.local`).

## Service endpoints

- API base URL: `http://localhost:9003`
- MySQL: `localhost:3306`

## API documentation files

- OpenAPI spec: `docs/openapi.yaml`
- Postman collection: `docs/postman_collection.json`

## Postman usage

1. Import `docs/postman_collection.json`.
2. Set collection variables:
   - `base_url` -> `http://localhost:9003`
   - `streamer_token` -> token from `token/generate 1 streamer`
   - `audience_token` -> token from `token/generate 2 audience`
3. Run `Streamer -> Start Room` first to auto-populate `livestream_id`.
4. Then run audience endpoints.

## Automated tests

Run all tests:

```bash
docker compose run --rm -T api ./vendor/bin/phpunit -c phpunit.xml.dist
```

Run only functional API contract tests:

```bash
docker compose run --rm -T api ./vendor/bin/phpunit -c phpunit.xml.dist tests/Functional/ApiContractTest.php
```
