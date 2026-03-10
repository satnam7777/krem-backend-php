# Traefik wildcard routing (SaaS feel)

This adds Traefik reverse proxy so you don't need separate port mapping per service,
and you can use wildcard tenant subdomains **without editing /etc/hosts**.

We use **sslip.io** which resolves any `*.127.0.0.1.sslip.io` to `127.0.0.1` automatically.

## Start
1) Copy env:
   cp .env.docker.example .env

2) Start with Traefik override:
   docker compose -f docker-compose.yml -f docker-compose.traefik.yml up -d --build

3) Run central migrations:
   docker compose exec app php artisan migrate

4) Create a tenant with domain:
   docker compose exec app php artisan tenant:create "Salon Demo" --domain=demo.127.0.0.1.sslip.io --migrate

## Use
Open:
- http://demo.127.0.0.1.sslip.io:8080

Traefik dashboard:
- http://localhost:8081

## Notes
- With Traefik enabled, you should NOT use the old nginx port binding in docker-compose.yml.
- If you already started without Traefik, run:
  docker compose down
  then start with the override command above.
