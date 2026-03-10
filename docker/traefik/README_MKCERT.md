# HTTPS locally with Traefik + mkcert

This enables **https://tenant.127.0.0.1.sslip.io** locally.

## 1) Install mkcert
- macOS: brew install mkcert
- Windows: choco install mkcert
- Linux: see mkcert docs

Then:
mkcert -install

## 2) Create certs for wildcard host
From project root:
mkdir -p docker/traefik/certs
mkcert -key-file docker/traefik/certs/local.key -cert-file docker/traefik/certs/local.crt "*.127.0.0.1.sslip.io" "127.0.0.1.sslip.io"

## 3) Start with HTTPS override
docker compose -f docker-compose.yml -f docker-compose.traefik.yml -f docker-compose.https.yml up -d --build

## 4) Use
- https://demo.127.0.0.1.sslip.io
Traefik dashboard:
- http://localhost:8081
