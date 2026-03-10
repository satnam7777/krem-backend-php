# Krema.ba Backend - Finished Pack (Central Identity)

## Start
```bash
cp .env.docker.example .env
docker compose up -d --build
```

## Install deps
```bash
docker exec -it krema_app bash -lc "composer install"
```

## Central migrations
```bash
docker exec -it krema_app bash -lc "php artisan migrate --path=database/migrations/central"
```

## Bootstrap demo (optional)
```bash
docker exec -it krema_app bash -lc "php artisan krema:bootstrap-demo --host=demo.local --email=demo.owner@example.com --password='Password123!'"
```

## Run tests
```bash
docker exec -it krema_app bash -lc "php artisan test"
```

## Superadmin onboarding API
`POST /api/onboarding/bootstrap` (superadmin token)

```json
{
  "tenant_name": "Salon Group A",
  "tenant_host": "a.local",
  "owner_name": "Ana Owner",
  "owner_email": "ana@a.local",
  "owner_password": "Password123!",
  "first_salon_name": "Salon A - Main",
  "timezone": "Europe/Sarajevo"
}
```
