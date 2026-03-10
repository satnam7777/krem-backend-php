# Docker (Production-grade dev stack)

Services:
- app (php-fpm)
- nginx
- postgres (central + tenant DBs inside same cluster)
- redis
- worker (queue)
- scheduler (Laravel schedule:work)

## Quick start
1) Copy env:
   cp .env.docker.example .env

2) Build & start:
   docker compose up -d --build

3) Run central migrations:
   docker compose exec app php artisan migrate

4) Create tenant + migrate tenant DB:
   docker compose exec app php artisan tenant:create "Salon Demo" --domain=demo.localhost --migrate

5) Add host mapping (on your machine):
   127.0.0.1 demo.localhost

6) Hit:
   http://demo.localhost/api/tenant/ping  (if you included tenancy_example route)

Notes:
- Because this is DB-per-tenant, Postgres container will store multiple tenant databases.
- The DB user in this stack is a superuser, so CREATE DATABASE works out-of-box.
