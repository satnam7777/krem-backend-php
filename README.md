# Krema.ba Backend — TRUE Monorepo App (Modules 1–9 Integrated)

This repository is a **single Laravel app** with Modules 1–9 already merged into:
- `app/`
- `database/`
- `routes/`
- `tests/`

**Important:** `vendor/` is not included. Run `composer install`.

## Setup
1) `composer install`
2) `cp .env.example .env` and set DB/mail values
3) `php artisan key:generate`
4) `php artisan migrate`
5) `php artisan test`

## Routes
Routes are loaded from `routes/api.moduleX.php` via `routes/api.php`.

## Scheduler
Set cron:
`* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1`

## Production Notes
- Use queue worker for outbox/webhooks (recommended), otherwise cron commands run them.
- Configure MAIL + QUEUE + cache properly.
