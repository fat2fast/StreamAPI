# Yii2 Livestream API

Backend API for livestream management using PHP 8.2, Yii2, MySQL, and Docker.

## Project Overview

This project is a backend service for livestream management with two API clients:

- Streamer app: starts and closes livestream rooms.
- Audience app: lists active livestreams and views livestream detail.

Core business rule:

- One streamer can have only one active livestream at a time.

In practice, the system provides API-only flows (no frontend), token-based authorization, and cleanly separated business logic so behavior stays easy to review and test.

## API Overview

Streamer API:

- `POST /streamer/start_room`
- `POST /streamer/close_room`

Audience API:

- `GET /audience/livestreams`
- `GET /audience/livestreams/{livestream_id}`

## Quick start

1. Create environment file:

```bash
cp .env.example .env
```

2. Start services (includes dependency install + auto migrate):

```bash
./start.sh
```

3. Generate test tokens for seeded users:

```bash
docker compose exec api php yii token/generate 1 streamer
docker compose exec api php yii token/generate 2 audience
```

Important:

- `start.sh` runs service startup, dependency check/install, and Yii2 migrations in one command.
- `docker-compose.yml` loads runtime variables from `.env`.
- Initial seeded users are inserted by migration (`streamer@test.local`, `audience@test.local`).
- `JWT_SECRET` must be at least 32 characters for HS256.

If you changed `.env`, recreate API container to reload env values:

```bash
docker compose up -d --build api
```

## Docker setup

- API base URL: `http://localhost:9003`
- MySQL: `localhost:3306`
- One-command startup: `./start.sh`
- Manual migration command (if you need to rerun):
  - `docker compose exec api php yii migrate --interactive=0`

## Data Model

Main table for livestream flow: `livestreams`

- `id`
- `streamer_id`
- `title`
- `status` (`active` or `closed`)
- `started_at`
- `closed_at`

Support table: `users`

- `id`, `username`, `email`, `role`

How this supports the business rule:

- Each livestream session is stored as one database record.
- Active vs closed lifecycle is tracked by `status` and `closed_at`.
- A DB-level active-session uniqueness guard is added to prevent multiple active sessions for the same streamer under race conditions.

## API documentation files

- OpenAPI source spec: `docs/openapi.yaml`
- Runtime OpenAPI spec (served by API): `app/docs/openapi.yaml`
- Postman collection: `docs/postman_collection.json`

## Swagger UI (dev only)

Swagger endpoints are available only when `APP_ENV=dev`:

- `GET /docs` -> Swagger UI
- `GET /docs/openapi.yaml` -> OpenAPI spec used by Swagger UI

Open in browser:

`http://localhost:9003/docs`

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

## Architecture overview

- `app/src/Domain` contains framework-agnostic entities, enums, and repository contracts.
- `app/src/Application` contains use cases, DTOs, and business orchestration.
- `app/infrastructure` contains Yii-specific adapters (repository, JWT service).
- `app/controllers` is a thin HTTP layer with auth and response mapping.

This keeps business logic independent from Yii while still using Yii2 for delivery and infrastructure.

## What I am most proud of

Three technical decisions mattered most:

1. Enforcing the single active livestream rule safely
   - The rule is checked at use-case level for clear business intent.
   - A DB-level uniqueness guard for active sessions adds protection under concurrent requests.
   - This combination avoids relying only on controller checks.

2. Layered boundaries (Domain / Application / Infrastructure)
   - Domain models and contracts stay free from Yii dependencies.
   - Application orchestrates business use cases and validation.
   - Infrastructure adapts storage/auth details to framework components.
   - Result: better separation of concerns and cleaner reviews.

3. Framework-agnostic business logic
   - Use cases depend on repository interfaces, not ActiveRecord directly.
   - This makes behavior testable in isolation and easier to migrate or refactor later.
   - It also keeps automated tests faster and more maintainable.

## PHP 8.2 highlights (vs PHP 7.2)

The codebase uses PHP 8.2 features that improve readability and safety compared with 7.2:

- `readonly` classes/properties for immutable DTO/entity shapes.
- Enums (`LivestreamStatus`) instead of string constants everywhere.
- Constructor property promotion for less boilerplate.
- Typed properties and return types across layers.
- `match`-style mapping in exception mapper for explicit, maintainable branching.

Compared with PHP 7.2, this reduces accidental mutation, improves static analysis, and keeps intent clearer during review.

## Production-ready checklist

- [x] All 4 endpoints pass functional and integration-level regression tests.
- [x] JWT secret is strong and rotated per environment.
- [x] `APP_ENV=prod` disables `/docs` endpoints.
- [x] Migrations and rollback path are validated on clean DB.
- [x] Logs and error responses do not leak stack traces.
- [x] README runbook works on a fresh machine.

## Release handoff notes

- Docker startup is one command: `./start.sh`.
- `start.sh` also applies migrations automatically.
- Manual migration rerun (if needed):
  - `docker compose exec api php yii migrate --interactive=0`
- Suggested submission artifacts:
  - source repo
  - `docs/openapi.yaml`
  - `docs/postman_collection.json`
  - this README
