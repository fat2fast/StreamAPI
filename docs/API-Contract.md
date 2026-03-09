# API Contract - Phase 0

This document locks the API contract before implementation.

## Base assumptions

- Base URL (local): `http://localhost`
- Auth: `Authorization: Bearer <JWT>`
- Content type for write APIs: `application/json`
- Time format in responses: ISO-8601 UTC (example: `2026-03-09T08:30:00Z`)

## Response envelopes

### Success (single object)

```json
{
  "data": {},
  "message": "OK"
}
```

### Success (list)

```json
{
  "data": [],
  "total": 0,
  "message": "OK"
}
```

### Error

```json
{
  "error": "ERROR_CODE",
  "message": "Human readable message"
}
```

## Role matrix

| Endpoint | Method | Streamer | Audience |
|---|---|---|---|
| `/streamer/start_room` | POST | Allowed | Forbidden (403) |
| `/streamer/close_room` | POST | Allowed | Forbidden (403) |
| `/audience/livestreams` | GET | Forbidden (403) | Allowed |
| `/audience/livestreams/{livestream_id}` | GET | Forbidden (403) | Allowed |

## Endpoint contracts

### 1) Start livestream room

- Method: `POST`
- URL: `/streamer/start_room`
- Role required: `streamer`

Request body:

```json
{
  "title": "Live coding session"
}
```

Validation:

- `title` is required
- `title` is string
- `title` length: 1..255

Success response (`201`):

```json
{
  "data": {
    "id": 101,
    "streamer_id": 1,
    "title": "Live coding session",
    "status": "active",
    "started_at": "2026-03-09T08:30:00Z",
    "closed_at": null
  },
  "message": "OK"
}
```

Business conflict (`409`):

```json
{
  "error": "CONFLICT",
  "message": "Streamer already has an active session"
}
```

### 2) Close livestream room

- Method: `POST`
- URL: `/streamer/close_room`
- Role required: `streamer`
- Request body: none

Success response (`200`):

```json
{
  "data": {
    "id": 101,
    "streamer_id": 1,
    "title": "Live coding session",
    "status": "closed",
    "started_at": "2026-03-09T08:30:00Z",
    "closed_at": "2026-03-09T09:15:00Z"
  },
  "message": "OK"
}
```

Not found (`404`):

```json
{
  "error": "NOT_FOUND",
  "message": "Active livestream not found"
}
```

### 3) List active livestreams

- Method: `GET`
- URL: `/audience/livestreams`
- Role required: `audience`

Success response (`200`):

```json
{
  "data": [
    {
      "id": 101,
      "streamer_id": 1,
      "title": "Live coding session",
      "status": "active",
      "started_at": "2026-03-09T08:30:00Z",
      "closed_at": null
    }
  ],
  "total": 1,
  "message": "OK"
}
```

Rule:

- Only records with `status = active` are returned.

### 4) Get livestream detail

- Method: `GET`
- URL: `/audience/livestreams/{livestream_id}`
- Role required: `audience`

Path params:

- `livestream_id`: integer, required

Success response (`200`):

```json
{
  "data": {
    "id": 101,
    "streamer_id": 1,
    "title": "Live coding session",
    "status": "active",
    "started_at": "2026-03-09T08:30:00Z",
    "closed_at": null
  },
  "message": "OK"
}
```

Not found (`404`):

```json
{
  "error": "NOT_FOUND",
  "message": "Livestream not found"
}
```

Rule:

- Detail endpoint only exposes active livestream sessions.

## Auth and common errors

Missing token (`401`):

```json
{
  "error": "UNAUTHORIZED",
  "message": "Missing bearer token"
}
```

Invalid/expired token (`401`):

```json
{
  "error": "UNAUTHORIZED",
  "message": "Invalid or expired token"
}
```

Role mismatch (`403`):

```json
{
  "error": "FORBIDDEN",
  "message": "You are not allowed to access this resource"
}
```

Invalid payload (`400`):

```json
{
  "error": "BAD_REQUEST",
  "message": "Invalid request payload"
}
```

## Error code map

| HTTP | error | Meaning |
|---|---|---|
| 400 | `BAD_REQUEST` | Input is invalid |
| 401 | `UNAUTHORIZED` | Missing, invalid, or expired token |
| 403 | `FORBIDDEN` | Authenticated but role not allowed |
| 404 | `NOT_FOUND` | Requested livestream resource not found |
| 409 | `CONFLICT` | Streamer already has an active livestream |

## Contract decisions locked in Phase 0

- `close_room` without active session returns `404`.
- `audience` endpoints only return active livestream sessions.
- `start_room` success returns `201`.
- All errors follow one stable envelope: `error` + `message`.
