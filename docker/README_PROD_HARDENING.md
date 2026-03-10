# Production hardening pack (what changed)

## API Rate limiting
- Adds `RouteServiceProvider` with rate limiter `api`.
- Applies `throttle:api` to API middleware group.
- Configure via env: `KREMA_API_RPM=120`.

## Request ID + log context
- Middleware `AddRequestId` generates/propagates `X-Request-Id`.
- Middleware `LogContext` adds `tenant_id`, `user_id`, `request_id` to logs via `Log::withContext()`.

## JSON logs (optional)
- Adds a `json` logging channel writing to `storage/logs/laravel.json.log`.

## Nginx security headers
- Adds basic security headers (deny framing, nosniff, etc.)

## Docker healthchecks + restart policy
- Adds `restart: unless-stopped` and healthchecks for Postgres/Redis.
