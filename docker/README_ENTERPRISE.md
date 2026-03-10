# Enterprise pack

Adds:
- CSP + HSTS (when HTTPS) via `SecurityHeaders` middleware
- Central audit table `platform_audit_logs` + helper service
- HTTPS local override with mkcert (Traefik)
- Secrets guidance + optional resource limits override

## Start (HTTP)
docker compose -f docker-compose.yml -f docker-compose.traefik.yml up -d --build

## Start (HTTPS local)
1) Create certs (see docker/traefik/README_MKCERT.md)
2) Start:
docker compose -f docker-compose.yml -f docker-compose.traefik.yml -f docker-compose.https.yml up -d --build

Then browse:
- https://demo.127.0.0.1.sslip.io:8443
